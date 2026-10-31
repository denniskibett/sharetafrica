<?php

namespace App\Integrations\Simulator;

use App\Integrations\Contracts\PaymentRail;
use App\Integrations\Contracts\ProvidesStatements;
use App\Integrations\RailResult;
use App\Models\RailRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * A stand-in PSP for building and testing before Cashia and Daraja are integrated. It answers like a real one:
 * pending at first, then (after config rails.simulator.delay seconds) succeeded, failed or, for a while, unknown.
 * Outcome by the last four digits of the number: 0000 fails, 9999 stays unknown for the first status checks.
 * Its statement reports what "really" happened, whether or not Sharet heard about it, so reconciliation can be tested.
 * It moves no money and needs no credentials. Never enable it in production.
 */
class SimulatorRail implements PaymentRail, ProvidesStatements
{
    public function name(): string
    {
        return 'simulator';
    }

    public function collect(RailRequest $request): RailResult
    {
        return RailResult::pending('SIM-'.strtoupper(Str::random(10)));
    }

    public function payout(RailRequest $request): RailResult
    {
        return RailResult::pending('SIM-'.strtoupper(Str::random(10)));
    }

    public function status(RailRequest $request): RailResult
    {
        $cfg = config('rails.simulator');
        if ($request->created_at->diffInSeconds(now()) < $cfg['delay']) {
            return RailResult::pending($request->provider_reference);
        }
        if (! self::succeeds($request)) {
            return RailResult::failed($request->kind === 'collect' ? 'The customer cancelled the M-PESA prompt.' : 'The recipient account could not be credited.', $request->provider_reference);
        }
        if (str_ends_with(self::digits($request), $cfg['slow_suffix']) && $request->checks < $cfg['slow_checks']) {
            return RailResult::unknown('No answer from the provider yet.');
        }

        return RailResult::succeeded(self::receipt($request), $request->provider_reference);
    }

    /** Every request the simulator received that day and that really went through, plus any extra lines added by tests. */
    public function statement(string $day): array
    {
        $lines = RailRequest::where('rail', 'simulator')->whereDate('created_at', $day)->orderBy('id')->get()
            ->filter(fn (RailRequest $r) => self::succeeds($r))
            ->map(fn (RailRequest $r) => [
                'line_ref' => $r->provider_reference ?? 'SIM-REQ-'.$r->id,
                'direction' => $r->kind === 'collect' ? 'in' : 'out',
                'amount' => $r->amount,
                'party' => $r->party,
                'receipt' => self::receipt($r),
                'occurred_at' => $r->created_at,
                'provider_reference' => $r->provider_reference,
                'raw' => ['simulated' => true],
            ])->values()->all();

        return array_merge($lines, Cache::get("simulator:statement:{$day}", []));
    }

    /** Test helper: money the provider says moved that Sharet knows nothing about (e.g. a deposit with a wrong account). */
    public static function addStatementLine(string $day, string $direction, int $amount, string $party): string
    {
        $ref = 'SIM-X-'.strtoupper(Str::random(8));
        $extra = Cache::get("simulator:statement:{$day}", []);
        $extra[] = ['line_ref' => $ref, 'direction' => $direction, 'amount' => $amount, 'party' => $party, 'receipt' => 'X'.strtoupper(Str::random(9)),
            'occurred_at' => Carbon::parse($day)->setTime(12, 0), 'provider_reference' => null, 'raw' => ['simulated' => true, 'unidentified' => true]];
        Cache::put("simulator:statement:{$day}", $extra, now()->addDays(7));

        return $ref;
    }

    private static function succeeds(RailRequest $r): bool
    {
        return ! str_ends_with(self::digits($r), config('rails.simulator.fail_suffix'));
    }

    private static function digits(RailRequest $r): string
    {
        return preg_replace('/\D/', '', $r->party);
    }

    /** Stable per request, so the statement and the status answer always carry the same receipt. */
    private static function receipt(RailRequest $r): string
    {
        return 'S'.strtoupper(substr(sha1($r->reference), 0, 9));
    }
}
