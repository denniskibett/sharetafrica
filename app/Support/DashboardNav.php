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
     * label, route, icon (heroicon outline name), tone (sidebar colour), roles (null = everyone), can (Spatie permission), company (non-admins need a company), active (route patterns)
     */
    protected static function groups(): array
    {
        return [
            [
                'title' => 'Menu',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'squares-2x2', 'tone' => 'violet',
                        'active' => ['dashboard', 'waiting_list.show', 'onboarding.*']],
                ],
            ],
            [
                'title' => 'Wallet',
                'roles' => ['individual'],
                'items' => [
                    ['label' => 'Wallet', 'route' => 'wallet.index', 'icon' => 'wallet', 'tone' => 'violet'],
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
                    ['label' => 'Business settings', 'route' => 'settings.company', 'icon' => 'building-storefront', 'tone' => 'slate'],
                    ['label' => 'Integrations', 'route' => 'integrations.index', 'icon' => 'puzzle-piece', 'tone' => 'violet', 'can' => 'integrations.manage', 'company' => true],
                    ['label' => 'API keys', 'route' => 'api-keys.index', 'icon' => 'key', 'tone' => 'orange', 'can' => 'developer.api_keys'],
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
                    ['label' => 'Business settings', 'route' => 'settings.company', 'icon' => 'building-storefront', 'tone' => 'slate'],
                    ['label' => 'Integrations', 'route' => 'integrations.index', 'icon' => 'puzzle-piece', 'tone' => 'violet', 'can' => 'integrations.manage', 'company' => true],
                    ['label' => 'API keys', 'route' => 'api-keys.index', 'icon' => 'key', 'tone' => 'orange', 'can' => 'developer.api_keys'],
                ],
            ],
            [
                'title' => 'Developers',
                'roles' => ['techie'],
                'items' => [
                    ['label' => 'API keys', 'route' => 'api-keys.index', 'icon' => 'key', 'tone' => 'amber', 'can' => 'developer.api_keys'],
                    ['label' => 'Webhooks', 'route' => 'developer.webhooks.index', 'icon' => 'bolt', 'tone' => 'violet'],
                    ['label' => 'Request logs', 'route' => 'developer.logs', 'icon' => 'command-line', 'tone' => 'slate'],
                    ['label' => 'Sandbox', 'route' => 'developer.sandbox', 'icon' => 'beaker', 'tone' => 'teal'],
                ],
            ],
            [
                'title' => 'Operations',
                'roles' => self::OPS,
                'items' => [
                    ['label' => 'Website analytics', 'route' => 'admin.analytics.index', 'icon' => 'chart-bar', 'tone' => 'teal', 'can' => 'analytics.view',
                        'roles' => ['support', 'operations'], 'active' => ['admin.analytics.*']],
                    ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'document-chart-bar', 'tone' => 'violet', 'can' => 'admin.users', 'roles' => ['support', 'operations']],
                    ['label' => 'Reconciliation', 'route' => 'ops.reconciliation.index', 'icon' => 'scale', 'tone' => 'indigo'],
                    ['label' => 'Ledger', 'route' => 'ledger.index', 'icon' => 'book-open', 'tone' => 'violet', 'can' => 'admin.reconciliation', 'active' => ['ledger.*']],
                    ['label' => 'Treasury pools', 'route' => 'ops.treasury.index', 'icon' => 'circle-stack', 'tone' => 'emerald'],
                    ['label' => 'Live rails', 'route' => 'ops.rails.index', 'icon' => 'signal', 'tone' => 'teal'],
                    ['label' => 'Support tickets', 'route' => 'ops.support.index', 'icon' => 'chat-bubble-left-right', 'tone' => 'rose'],
                ],
            ],
            [
                'title' => 'Administration',
                'roles' => self::ADMIN,
                'items' => [
                    ['label' => 'Website analytics', 'route' => 'admin.analytics.index', 'icon' => 'chart-bar', 'tone' => 'sky', 'can' => 'analytics.view',
                        'active' => ['admin.analytics.*']],
                    ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'document-chart-bar', 'tone' => 'violet'],
                    ['label' => 'User management', 'route' => 'users.index', 'icon' => 'users', 'tone' => 'indigo', 'active' => ['users.*']],
                    ['label' => 'Waiting list', 'route' => 'admin.waiting_list', 'icon' => 'queue-list', 'tone' => 'amber'],
                    ['label' => 'Enquiries', 'route' => 'admin.contact.index', 'icon' => 'inbox-arrow-down', 'tone' => 'sky', 'can' => 'waiting_list.invite'],
                    ['label' => 'Email designs', 'route' => 'admin.emails.index', 'icon' => 'envelope', 'tone' => 'rose', 'can' => 'admin.system',
                        'active' => ['admin.emails.*']],
                    ['label' => 'Integrations', 'route' => 'integrations.index', 'icon' => 'puzzle-piece', 'tone' => 'violet', 'can' => 'integrations.manage', 'company' => true],
                    ['label' => 'API keys', 'route' => 'api-keys.index', 'icon' => 'key', 'tone' => 'orange', 'can' => 'developer.api_keys'],
                    ['label' => 'Roles & permissions', 'route' => 'admin.roles.index', 'icon' => 'shield-check', 'tone' => 'emerald', 'can' => 'admin.roles'],
                    ['label' => 'System', 'route' => 'admin.system.index', 'icon' => 'adjustments-horizontal', 'tone' => 'slate', 'can' => 'admin.system'],
                ],
            ],
            [
                'title' => 'Website',
                'roles' => self::ADMIN,
                'items' => [
                    ['label' => 'Companies', 'route' => 'admin.group-companies.index', 'icon' => 'building-office-2', 'tone' => 'teal', 'can' => 'admin.system'],
                    ['label' => 'Team', 'route' => 'admin.team.index', 'icon' => 'user-group', 'tone' => 'rose', 'can' => 'admin.system'],
                    ['label' => 'Blog', 'route' => 'admin.posts.index', 'icon' => 'newspaper', 'tone' => 'sky', 'can' => 'admin.system', 'active' => ['admin.posts.*']],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.show', 'icon' => 'user-circle', 'tone' => 'rose'],
                    ['label' => 'Account settings', 'route' => 'profile.edit', 'icon' => 'cog-6-tooth', 'tone' => 'slate'],
                    ['label' => 'Sharet website', 'route' => 'home', 'icon' => 'globe-alt', 'tone' => 'teal'],
                ],
            ],
        ];
    }

    /** Customers (personal, merchant, business, developer accounts) get the Sharet app look; staff get the admin dashboard. */
    public static function isCustomer(?User $user): bool
    {
        return $user !== null && ! $user->hasAnyRole(self::OPS);
    }

    /** The lane's main action for the phone tab bar: [label, route, icon]. */
    public static function laneAction(?User $user): array
    {
        return match ($user?->intent) {
            'merchant' => ['Accept', 'merchant.accept', 'banknotes'],
            'business' => ['Pay', 'business.pay', 'paper-airplane'],
            'techie' => ['API', 'api-keys.index', 'key'],
            default => ['Send', 'wallet.transfer', 'paper-airplane'],
        };
    }

    /** Groups this user may see, each item resolved to url/active/ready. */
    public static function for(?User $user): array
    {
        $out = [];
        $soon = [];

        foreach (self::groups() as $group) {
            if (! self::allowed($user, $group['roles'] ?? null)) {
                continue;
            }

            $items = [];
            foreach ($group['items'] as $item) {
                if (! self::allowed($user, $item['roles'] ?? null) || (isset($item['can']) && ! $user?->can($item['can']))
                    || (! empty($item['company']) && ! $user?->company_id && ! $user?->hasAnyRole(self::ADMIN))) {
                    continue;
                }
                $ready = Route::has($item['route']);
                $items[] = $item + [
                    'ready' => $ready,
                    'url' => $ready ? route($item['route']) : null,
                    'current' => $ready && request()->routeIs(...($item['active'] ?? [$item['route']])),
                ];
            }

            // Pages not built yet are collected into one "Coming soon" group at the bottom
            $live = array_values(array_filter($items, fn ($i) => $i['ready']));
            foreach (array_filter($items, fn ($i) => ! $i['ready']) as $item) {
                $soon[$item['label']] ??= $item;
            }
            if ($live) {
                $out[] = ['title' => $group['title'], 'items' => $live];
            }
        }

        if (! empty($soon)) {
            $out[] = ['title' => 'Coming soon', 'items' => array_values($soon), 'soon' => true];
        }

        return $out;
    }

    /** The role's own group (for the home page cards), or null. */
    public static function roleGroup(?User $user, string $title): ?array
    {
        return collect(self::for($user))->firstWhere('title', $title);
    }

    /** Lanes come from Spatie roles or, as on the live site, from users.intent (most people have no role). */
    protected static function allowed(?User $user, ?array $roles): bool
    {
        return $roles === null || ($user && ($user->hasAnyRole($roles) || in_array($user->intent, $roles, true)));
    }
}
