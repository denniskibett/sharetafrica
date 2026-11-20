<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\Cloud9\Cloud9Rail;
use App\Ledger\Ledger;
use App\Ledger\Money;
use App\Models\RailRequest;
use App\Models\RailRoute;
use App\Models\RailRouteChange;
use App\Models\RampOrder;
use App\Security\Lockdown;
use App\Services\Integrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * System › Payment rails: which partner moves each kind of payment (the M-PESA paybill through Daraja, or Cloud9).
 * Every payment still goes through Sharet: one reference, one ledger. Changing a route takes two people: one proposes
 * with a reason, a different one approves with their password. Both are logged. Payments already moving finish on
 * the rail they started on.
 */
class RailsController extends Controller
{
    public function index(Ledger $ledger)
    {
        $routes = RailRoute::with('approver:id,name')->get()->keyBy('movement');
        $cloud9 = (array) Integrations::credentials('cloud9');

        return view('admin.rails.index', [
            'routes' => $routes,
            'movements' => RailRoute::MOVEMENTS,
            'rails' => $this->choosable(),
            'pending' => RailRouteChange::with('proposer:id,name')->where('status', 'pending')->latest()->get(),
            'history' => RailRouteChange::with(['proposer:id,name', 'decider:id,name'])->where('status', '!=', 'pending')->latest('decided_at')->limit(10)->get(),
            'balances' => [
                ['M-PESA paybill · working (1110)', $ledger->account('1110')->fresh()->natural()],
                ['M-PESA paybill · utility (1120)', $ledger->account('1120')->fresh()->natural()],
                ['Cloud9 · MAIN KES wallet (1160)', $ledger->account('1160')->fresh()->natural()],
            ],
            'legacy' => ['collect' => config('rails.collect') ?: config('rails.default'), 'payout' => config('rails.payout') ?: config('rails.default')],
            'cloud9' => ['connected' => filled($cloud9['api_key'] ?? null), 'environment' => $cloud9['environment'] ?? 'sandbox',
                'secrets' => ['PAYIN' => filled($cloud9['payin_secret'] ?? null), 'PAYOUT' => filled($cloud9['payout_secret'] ?? null)]],
            'callbackUrl' => route('rails.cloud9.callback'),
            'testList' => config('rails.collect_only_for'),
        ]);
    }

    /** One person proposes a route change, with a reason. Nothing moves until someone else approves it. */
    public function propose(Request $request)
    {
        $data = $request->validate([
            'movement' => ['required', Rule::in(array_keys(RailRoute::MOVEMENTS))],
            'rail' => ['required', Rule::in(array_keys($this->choosable()))],
            'fallback' => ['nullable', Rule::in(array_keys($this->choosable())), 'different:rail'],
            'audience' => ['required', Rule::in(array_keys(RailRoute::AUDIENCES))],
            'reason' => ['required', 'string', 'max:300'],
        ], ['fallback.different' => 'The fallback must be a different rail.']);
        foreach (array_filter([$data['rail'], $data['fallback'] ?? null]) as $rail) {
            $this->assertReady($rail);
        }
        RailRouteChange::where('movement', $data['movement'])->where('status', 'pending')->update(['status' => 'withdrawn', 'decided_at' => now(), 'decision_note' => 'Replaced by a newer proposal']);
        $change = RailRouteChange::create($data + ['proposed_by' => $request->user()->id, 'status' => 'pending']);
        Lockdown::log('payment rail change proposed', ['change' => $change->id] + $data);

        return back()->with('status', 'Proposed. A second person must approve it before any payment moves differently.');
    }

    /** A different person approves (fresh password): the route changes for new payments from now. */
    public function approve(Request $request, RailRouteChange $change)
    {
        abort_if($change->proposed_by === $request->user()->id, 403, 'A second person must approve a change you proposed.');
        DB::transaction(function () use ($request, $change) {
            $c = RailRouteChange::whereKey($change->id)->lockForUpdate()->firstOrFail();
            if ($c->status !== 'pending') {
                throw ValidationException::withMessages(['change' => 'This change is already '.$c->status.'.']);
            }
            $this->assertReady($c->rail);
            RailRoute::updateOrCreate(['movement' => $c->movement], [
                'rail' => $c->rail, 'fallback' => $c->fallback, 'audience' => $c->audience,
                'approved_by' => $request->user()->id, 'approved_at' => now(),
            ]);
            $c->update(['status' => 'approved', 'decided_by' => $request->user()->id, 'decided_at' => now()]);
        });
        Lockdown::log('payment rail change approved', ['change' => $change->id, 'movement' => $change->movement, 'rail' => $change->rail, 'audience' => $change->audience]);

        return back()->with('status', RailRoute::MOVEMENTS[$change->movement][0].' now runs on '.RailRoute::RAILS[$change->rail].' for new payments.');
    }

    /** The approver turns it down (with a reason), or the proposer withdraws it. */
    public function reject(Request $request, RailRouteChange $change)
    {
        $data = $request->validate(['note' => 'nullable|string|max:300']);
        abort_unless($change->status === 'pending', 422);
        $own = $change->proposed_by === $request->user()->id;
        $change->update(['status' => $own ? 'withdrawn' : 'rejected', 'decided_by' => $request->user()->id, 'decided_at' => now(), 'decision_note' => $data['note'] ?? null]);
        Lockdown::log('payment rail change '.($own ? 'withdrawn' : 'rejected'), ['change' => $change->id]);

        return back()->with('status', $own ? 'Withdrawn.' : 'Turned down.');
    }

    /**
     * Cloud9 sandbox checks, straight against the API (no ledger, no customer): read the wallets, send an M-PESA prompt
     * of KES 20 to the staff member's own number, or pay KES 10 to it. Sandbox only: never moves real money.
     */
    public function test(Request $request, Cloud9Rail $rail)
    {
        $data = $request->validate(['check' => 'required|in:wallets,prompt,payout,status', 'id' => 'nullable|string|max:20', 'kind' => 'nullable|in:deposit,payout']);
        $c = (array) Integrations::credentials('cloud9');
        if (($c['environment'] ?? 'sandbox') !== 'sandbox' && $data['check'] !== 'wallets') {
            throw ValidationException::withMessages(['check' => 'Test payments run only against the Cloud9 sandbox.']);
        }
        $phone = preg_replace('/\D/', '', (string) $request->user()->phone);
        if (in_array($data['check'], ['prompt', 'payout'], true) && ! $phone) {
            throw ValidationException::withMessages(['check' => 'Add your M-PESA number to your profile first: the test pays you.']);
        }
        $ref = 'SHTEST'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
        try {
            $res = match ($data['check']) {
                'wallets' => $rail->http()->get('wallets/'),
                'prompt' => $rail->http()->post('payin/deposits/init/', ['country' => 'KE', 'currency' => 'KES', 'amount' => 20, 'phone_number' => $phone,
                    'provider' => 'MPESA-C2B', 'reference' => $ref, 'narration' => 'Sharet', 'callback_url' => route('rails.cloud9.callback')]),
                'payout' => $rail->http()->post('payout/payouts/init/', ['country' => 'KE', 'currency' => 'KES', 'amount' => 10, 'provider' => 'MPESA-B2C',
                    'account' => $phone, 'reference' => $ref, 'narration' => 'Sharet test', 'callback_url' => route('rails.cloud9.callback')]),
                'status' => $rail->http()->get(($data['kind'] === 'payout' ? 'payout/payouts/' : 'payin/deposits/').$data['id'].'/'),
            };
            $body = $res->json();
            $result = ['check' => $data['check'], 'ok' => $res->successful(), 'status' => $res->status(),
                'summary' => $this->summary($data['check'], $res->successful(), (array) $body), 'body' => json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)];
        } catch (Throwable $e) {
            $result = ['check' => $data['check'], 'ok' => false, 'status' => 0, 'summary' => $e->getMessage(), 'body' => null];
        }
        Lockdown::log('cloud9 sandbox check', ['check' => $data['check'], 'ok' => $result['ok']]);

        return back()->with('cloud9_test', $result);
    }

    /** Every request on every rail, newest first, with the Sharet reference beside the partner's id and receipt. */
    public function activity(Request $request)
    {
        $rail = in_array($request->query('rail'), array_keys(RailRoute::RAILS), true) ? $request->query('rail') : null;
        $status = in_array($request->query('status'), ['pending', 'succeeded', 'failed', 'unknown'], true) ? $request->query('status') : null;
        $requests = RailRequest::query()->when($rail, fn ($q) => $q->where('rail', $rail))->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')->paginate(30)->withQueryString();
        $orders = RampOrder::whereIn('uuid', $requests->pluck('meta.order')->filter()->unique())->get()->keyBy('uuid');

        $today = RailRequest::where('created_at', '>=', now('Africa/Nairobi')->startOfDay()->utc())->get(['rail', 'status', 'meta', 'created_at', 'completed_at']);
        $stats = $today->groupBy('rail')->map(fn ($rows) => [
            'done' => $rows->where('status', 'succeeded')->count(),
            'final' => $rows->whereIn('status', ['succeeded', 'failed'])->count(),
            'charges' => $rows->sum(fn ($r) => (int) ($r->meta['charge'] ?? 0)),
            'median' => $rows->filter(fn ($r) => $r->completed_at)->map(fn ($r) => $r->completed_at->diffInSeconds($r->created_at, true))->median(),
        ]);

        return view('admin.rails.activity', compact('requests', 'orders', 'stats', 'rail', 'status'));
    }

    /** Rails a route can use here: the simulator only where it is allowed (never real money). */
    private function choosable(): array
    {
        $rails = RailRoute::RAILS;
        if (app()->isProduction() && ! config('rails.simulator.allow_in_production')) {
            unset($rails['simulator']);
        }

        return $rails;
    }

    /** A rail can only be switched on once it is connected. */
    private function assertReady(string $rail): void
    {
        $ready = match ($rail) {
            'cloud9' => filled(Integrations::credentials('cloud9')['api_key'] ?? null),
            'daraja' => (bool) Integrations::mpesa()[0],
            default => true,
        };
        if (! $ready) {
            throw ValidationException::withMessages(['rail' => RailRoute::RAILS[$rail].' is not connected yet: add its keys in Integrations first.']);
        }
    }

    private function summary(string $check, bool $ok, array $b): string
    {
        if (! $ok) {
            return 'Refused: '.($b['error'] ?? $b['errors'][0]['detail'] ?? $b['detail'] ?? 'see the response');
        }

        return match ($check) {
            'wallets' => collect($b['results'] ?? [])->map(fn ($w) => $w['wallet_id'].' '.$w['wallet_type'].' '.$w['currency'].' '.$w['available_balance']
                .(isset($w['mpesa_paybill']['paybill_number']) ? ' · paybill '.$w['mpesa_paybill']['paybill_number'].' account '.$w['mpesa_paybill']['account'] : ''))->implode("\n") ?: 'No wallets.',
            'prompt' => 'Prompt sent: deposit '.($b['id'] ?? '?').' is '.($b['status'] ?? '?').'. Check your phone, then check its status.',
            'payout' => 'Payout '.($b['id'] ?? '?').' is '.($b['status'] ?? '?').(isset($b['charge']) ? ' · charge KES '.$b['charge'] : '').'.',
            'status' => ($b['id'] ?? '?').' is '.($b['status'] ?? '?').(! empty($b['provider_reference']) ? ' · M-PESA '.$b['provider_reference'] : '').(! empty($b['message']) ? ' · '.$b['message'] : ''),
        };
    }

    public static function money(int $cents): string
    {
        return 'KES '.Money::format($cents);
    }
}
