<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * One menu definition for the dashboard: the sidebar and each role's home cards read it.
 * Items whose route is not built yet stay visible as "Soon" (no link) so every role sees its full map.
 */
class DashboardNav
{
    /** Staff roles that may use the operations desk. */
    public const OPS = ['support', 'operations', 'admin', 'super_admin'];

    public const ADMIN = ['admin', 'super_admin'];

    /**
     * label, route, icon (heroicon outline name), tone (sidebar colour), roles (null = everyone), active (route patterns)
     */
    protected static function groups(): array
    {
        return [
            [
                'title' => 'Menu',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'squares-2x2', 'tone' => 'violet',
                        'active' => ['dashboard', 'dashboard.individual', 'dashboard.merchant', 'dashboard.business', 'dashboard.developer', 'dashboard.admin', 'waiting_list.show']],
                ],
            ],
            [
                'title' => 'Wallet',
                'roles' => ['individual'],
                'items' => [
                    ['label' => 'Send money', 'route' => 'wallet.transfer', 'icon' => 'paper-airplane', 'tone' => 'emerald'],
                    ['label' => 'Receive money', 'route' => 'wallet.deposit', 'icon' => 'arrow-down-tray', 'tone' => 'sky'],
                    ['label' => 'My QR code', 'route' => 'individual.qr', 'icon' => 'qr-code', 'tone' => 'amber'],
                ],
            ],
            [
                'title' => 'Merchant',
                'roles' => ['merchant'],
                'items' => [
                    ['label' => 'Accept payments', 'route' => 'merchant.accept', 'icon' => 'banknotes', 'tone' => 'emerald'],
                    ['label' => 'Settlements', 'route' => 'merchant.settle', 'icon' => 'building-library', 'tone' => 'sky'],
                    ['label' => 'QR codes', 'route' => 'merchant.qr', 'icon' => 'qr-code', 'tone' => 'amber'],
                    ['label' => 'Reconciliation', 'route' => 'merchant.reconcile', 'icon' => 'scale', 'tone' => 'indigo'],
                ],
            ],
            [
                'title' => 'Business',
                'roles' => ['business'],
                'items' => [
                    ['label' => 'Invoices', 'route' => 'business.invoices.index', 'icon' => 'document-text', 'tone' => 'sky'],
                    ['label' => 'Pay suppliers', 'route' => 'business.pay', 'icon' => 'paper-airplane', 'tone' => 'emerald'],
                    ['label' => 'Trade finance', 'route' => 'business.trade-finance', 'icon' => 'globe-europe-africa', 'tone' => 'amber'],
                    ['label' => 'Treasury', 'route' => 'business.treasury', 'icon' => 'building-library', 'tone' => 'indigo'],
                ],
            ],
            [
                'title' => 'Developers',
                'roles' => ['techie'],
                'items' => [
                    ['label' => 'API keys', 'route' => 'developer.api-keys.index', 'icon' => 'key', 'tone' => 'amber'],
                    ['label' => 'Webhooks', 'route' => 'developer.webhooks.index', 'icon' => 'bolt', 'tone' => 'violet'],
                    ['label' => 'Request logs', 'route' => 'developer.logs', 'icon' => 'command-line', 'tone' => 'slate'],
                    ['label' => 'Sandbox', 'route' => 'developer.sandbox', 'icon' => 'beaker', 'tone' => 'teal'],
                ],
            ],
            [
                'title' => 'Operations',
                'roles' => self::OPS,
                'items' => [
                    ['label' => 'Operations desk', 'route' => 'dashboard.ops', 'icon' => 'lifebuoy', 'tone' => 'sky'],
                    ['label' => 'Reconciliation', 'route' => 'ops.reconciliation.index', 'icon' => 'scale', 'tone' => 'indigo'],
                    ['label' => 'Treasury pools', 'route' => 'ops.treasury.index', 'icon' => 'circle-stack', 'tone' => 'emerald'],
                    ['label' => 'Live rails', 'route' => 'ops.rails.index', 'icon' => 'signal', 'tone' => 'teal'],
                    ['label' => 'Support tickets', 'route' => 'ops.support.index', 'icon' => 'chat-bubble-left-right', 'tone' => 'rose'],
                ],
            ],
            [
                'title' => 'Administration',
                'roles' => self::ADMIN,
                'items' => [
                    ['label' => 'Waiting list', 'route' => 'admin.waiting_list', 'icon' => 'queue-list', 'tone' => 'amber'],
                    ['label' => 'Integrations', 'route' => 'admin.integrations.index', 'icon' => 'puzzle-piece', 'tone' => 'violet'],
                    ['label' => 'API keys', 'route' => 'admin.api-keys.index', 'icon' => 'key', 'tone' => 'orange'],
                    ['label' => 'Roles & permissions', 'route' => 'admin.roles.index', 'icon' => 'shield-check', 'tone' => 'emerald'],
                    ['label' => 'System settings', 'route' => 'admin.system.index', 'icon' => 'adjustments-horizontal', 'tone' => 'slate'],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.show', 'icon' => 'user-circle', 'tone' => 'rose'],
                    ['label' => 'Settings', 'route' => 'profile.edit', 'icon' => 'cog-6-tooth', 'tone' => 'slate'],
                    ['label' => 'Sharet website', 'route' => 'home', 'icon' => 'globe-alt', 'tone' => 'teal'],
                ],
            ],
        ];
    }

    /** Groups this user may see, each item resolved to url/active/ready. */
    public static function for(?User $user): array
    {
        $out = [];

        foreach (self::groups() as $group) {
            if (! self::allowed($user, $group['roles'] ?? null)) {
                continue;
            }

            $items = [];
            foreach ($group['items'] as $item) {
                if (! self::allowed($user, $item['roles'] ?? null)) {
                    continue;
                }
                $ready = Route::has($item['route']);
                $items[] = $item + [
                    'ready' => $ready,
                    'url' => $ready ? route($item['route']) : null,
                    'current' => $ready && request()->routeIs(...($item['active'] ?? [$item['route']])),
                ];
            }

            if ($items) {
                $out[] = ['title' => $group['title'], 'items' => $items];
            }
        }

        return $out;
    }

    /** The role's own group (for the home page cards), or null. */
    public static function roleGroup(?User $user, string $title): ?array
    {
        return collect(self::for($user))->firstWhere('title', $title);
    }

    protected static function allowed(?User $user, ?array $roles): bool
    {
        return $roles === null || ($user && $user->hasAnyRole($roles));
    }
}
