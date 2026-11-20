<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LedgerAccount;
use App\Models\RampOrder;
use App\Models\SupportCase;
use App\Models\User;
use App\Support\DashboardNav;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Header search (the dashboard's quick switcher, shown under the header box): jump to a page from the menu or open a
 * payment, person, business, support case or ledger account by typing. Each group asks the same permission or role as
 * the page it links to, so nobody gets a result they cannot open.
 * GET /search?q= → { results: [{ group, label, sub, url, icon }] }
 */
class SearchController extends Controller
{
    private const PER_GROUP = 5;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $results = $this->pages($user, $q);

        if (mb_strlen($q) >= 2) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q).'%';

            $groups = [
                // My payments: the same orders PayController@index lists for this person (pay.show checks ownership too)
                [true, 'Your payments', 'arrows-right-left', fn () => RampOrder::query()
                    ->where(fn ($w) => $w->where('user_id', $user->id)->orWhere('recipient_user_id', $user->id))
                    ->whereNotIn('status', ['quoted', 'expired', 'reversed'])
                    ->where(fn ($w) => $w->where('destination', 'like', $like)->orWhere('payer_phone', 'like', $like)->orWhere('note', 'like', $like)
                        ->when(preg_match('/^SH([0-9a-f]{4,8})$/i', $q, $m), fn ($w) => $w->orWhere('uuid', 'like', strtolower($m[1]).'%')))
                    ->latest('id')->limit(self::PER_GROUP)->get()
                    ->map(fn (RampOrder $o) => [($p = $o->present($user))['title'], $o->sharetReference().' · '.$p['signed'].' · '.$p['state']['label'], $p['url']])],

                // users.* routes: permission:admin.users
                [$user->can('admin.users'), 'People', 'users', fn () => User::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)
                        ->orWhere('handle', 'like', $like)->orWhere('sharet_id', 'like', $like))
                    ->orderBy('name')->limit(self::PER_GROUP)->get(['id', 'name', 'email', 'phone', 'handle'])
                    ->map(fn (User $u) => [$u->name, trim(($u->handle ? '@'.$u->handle.' · ' : '').($u->email ?: $u->phone)), route('users.show', $u)])],

                // admin.businesses.index: permission:admin.users; the link opens the tab the business sits on
                [$user->can('admin.users'), 'Businesses', 'building-storefront', fn () => Company::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('legal_name', 'like', $like)->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)->orWhere('registration_number', 'like', $like))
                    ->orderBy('name')->limit(self::PER_GROUP)->get()
                    ->map(function (Company $c) {
                        $tab = $c->verified_at ? 'approved' : (($c->settings['kyb_review']['status'] ?? null) === 'rejected' ? 'rejected' : 'pending');

                        return [$c->name, trim(\App\Http\Controllers\Admin\BusinessesController::TABS[$tab].' · '.($c->email ?: $c->phone), ' ·'),
                            route('admin.businesses.index', ['tab' => $tab])];
                    })],

                // ops.support.*: role:support|operations|admin|super_admin (the desk has no page per case)
                [$user->hasAnyRole(DashboardNav::OPS), 'Support cases', 'chat-bubble-left-right', fn () => SupportCase::with('customer:id,name')
                    ->where(fn ($w) => $w->where('number', 'like', $like)->orWhere('summary', 'like', $like))
                    ->latest('id')->limit(self::PER_GROUP)->get()
                    ->map(fn (SupportCase $c) => [$c->number.' · '.$c->summary,
                        trim(($c->customer?->name ? $c->customer->name.' · ' : '').(SupportCase::STATUSES[$c->status][0] ?? ucfirst((string) $c->status))), route('ops.support.index')])],

                // ledger.*: permission:admin.reconciliation
                [$user->can('admin.reconciliation'), 'Ledger accounts', 'book-open', fn () => LedgerAccount::query()
                    ->where(fn ($w) => $w->where('code', 'like', $like)->orWhere('name', 'like', $like)
                        ->when(ctype_digit($q), fn ($w) => $w->orWhere('owner_key', 'like', '%:'.$q)))   // a user id finds their wallet
                    ->orderBy('code')->limit(self::PER_GROUP)->get()
                    ->map(fn (LedgerAccount $a) => [$a->code.' '.$a->name, trim(ucfirst((string) $a->type).' · '.($a->owner_key === 'system' ? 'Sharet' : class_basename(strtok((string) $a->owner_key, ':')).' '.substr((string) strrchr((string) $a->owner_key, ':'), 1))), route('ledger.show', $a->id)])],
            ];

            foreach ($groups as [$allowed, $group, $icon, $query]) {
                if (! $allowed) {
                    continue;
                }
                try {
                    foreach ($query() as [$label, $sub, $url]) {
                        $results[] = compact('group', 'label', 'sub', 'url', 'icon');
                    }
                } catch (\Throwable $e) {
                    report($e); // one broken group must not break the search box
                }
            }
        }

        return response()->json(['results' => $results])->header('Cache-Control', 'private, no-store');
    }

    /** Menu pages this person's sidebar shows (DashboardNav), filtered by the query. */
    private function pages(User $user, string $q): array
    {
        $needle = mb_strtolower($q);
        $out = [];
        foreach (DashboardNav::for($user) as $group) {
            foreach ($group['items'] as $item) {
                if (! ($item['ready'] ?? false) || isset($out[$item['label']])) {
                    continue;
                }
                if ($needle !== '' && ! str_contains(mb_strtolower($item['label'].' '.$group['title']), $needle)) {
                    continue;
                }
                $out[$item['label']] = ['group' => 'Go to', 'label' => $item['label'], 'sub' => $group['title'], 'url' => $item['url'], 'icon' => $item['icon']];
            }
        }

        return array_slice(array_values($out), 0, $q === '' ? 8 : 6);
    }

    /** Every icon a result can carry, so the header renders each once (heroicon outline names). */
    public static function icons(?User $user): array
    {
        return collect(DashboardNav::for($user))->flatMap(fn ($g) => array_column($g['items'], 'icon'))
            ->merge(['arrows-right-left', 'users', 'building-storefront', 'chat-bubble-left-right', 'book-open', 'magnifying-glass'])
            ->unique()->values()->all();
    }
}
