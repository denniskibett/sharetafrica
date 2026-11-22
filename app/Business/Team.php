<?php

namespace App\Business;

use App\Ledger\Money;
use App\Models\BusinessMember;
use App\Models\Company;
use App\Models\User;
use App\Support\Handle;
use App\Support\Identity;
use App\Support\TransactionPin;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

/**
 * Who can act for a company's wallet in the app (canvas "Phase 2 · Mobile app · Business", Team and roles):
 *   owner   everything: adds the team, sets approval limits, approves new payees at once
 *   maker   prepares payments and batches; never approves their own
 *   checker approves or rejects with a reason, up to their limit; cannot edit
 *   viewer  sees the balance and statements only
 * The person who registered the business (the first account linked to it) is its owner until they add others.
 */
final class Team
{
    public const ROLES = ['owner', 'maker', 'checker', 'viewer'];

    private const CAN = [
        'view' => ['owner', 'maker', 'checker', 'viewer'],
        'prepare' => ['owner', 'maker'],
        'approve' => ['owner', 'checker'],
        'team' => ['owner'],
        'trade' => ['owner', 'maker'],
    ];

    /** This person's place in a business, or null. The registering person becomes owner the first time. */
    public static function membership(User $user): ?BusinessMember
    {
        if ($user->is_business_wallet) {
            return null;
        }
        if ($user->company_id && ($company = Company::find($user->company_id))) {
            self::ensureOwner($company);
        }

        return BusinessMember::where('user_id', $user->id)->orderBy('id')->first();
    }

    /** A company with nobody on its team yet: the first person linked to it (who registered it) is the owner. */
    public static function ensureOwner(Company $company): void
    {
        if (BusinessMember::where('company_id', $company->id)->exists()) {
            return;
        }
        $first = User::where('company_id', $company->id)->where('is_business_wallet', false)->orderBy('id')->first();
        $first && BusinessMember::firstOrCreate(['company_id' => $company->id, 'user_id' => $first->id], ['role' => 'owner']);
    }

    public static function can(?BusinessMember $m, string $action): bool
    {
        return $m && in_array($m->role, self::CAN[$action] ?? [], true);
    }

    /** Stops the request with 403 unless this member may do it. */
    public static function authorize(?BusinessMember $m, string $action): BusinessMember
    {
        if (! $m) {
            self::deny('not_a_member', 'You are not on a business team on Sharet. The business owner can add you in Team.');
        }
        if (! self::can($m, $action)) {
            self::deny('not_allowed', match ($action) {
                'prepare', 'trade' => 'Only the owner or a maker can prepare payments.',
                'approve' => 'Only the owner or a checker can approve payments.',
                'team' => 'Only the owner can change the team.',
                default => 'You can\'t do that in this business.',
            });
        }

        return $m;
    }

    public static function deny(string $error, string $message, int $status = 403): never
    {
        throw new HttpResponseException(response()->json(['error' => $error, 'message' => $message], $status));
    }

    /** How much this member may approve on their own, in cents; null = no limit. */
    public static function approvalLimit(BusinessMember $m): ?int
    {
        if ($m->role === 'owner') {
            return $m->approval_limit;   // the owner has no limit unless they set one on themselves
        }

        return $m->approval_limit ?? (int) config('business.default_checker_limit');
    }

    /** The owner adds an existing Sharet account holder (by phone, @name or email) with a role, using their PIN. */
    public static function add(BusinessMember $owner, string $who, string $role, ?string $limit, ?string $pin): BusinessMember
    {
        self::authorize($owner, 'team');
        TransactionPin::check($owner->user, $pin);
        self::role($role);
        $person = self::find($who) ?? throw ValidationException::withMessages(['who' => 'No Sharet account has that phone, @name or email. Ask them to join Sharet first.']);
        if (! in_array($person->kyc_level, ['basic', 'standard'], true)) {
            throw ValidationException::withMessages(['who' => $person->name.' needs a verified Sharet account first.']);
        }
        if (BusinessMember::where('company_id', $owner->company_id)->where('user_id', $person->id)->exists()) {
            throw ValidationException::withMessages(['who' => $person->name.' is already on the team.']);
        }

        return BusinessMember::create(['company_id' => $owner->company_id, 'user_id' => $person->id, 'role' => $role,
            'approval_limit' => self::limit($limit), 'added_by' => $owner->user_id]);
    }

    public static function change(BusinessMember $owner, BusinessMember $member, ?string $role, ?string $limit, bool $clearLimit, ?string $pin): BusinessMember
    {
        self::authorize($owner, 'team');
        TransactionPin::check($owner->user, $pin);
        if ($role !== null) {
            self::role($role);
            if ($member->role === 'owner' && $role !== 'owner' && self::owners($member->company_id) === 1) {
                throw ValidationException::withMessages(['role' => 'A business needs an owner. Make someone else owner first.']);
            }
            $member->role = $role;
        }
        if ($limit !== null || $clearLimit) {
            $member->approval_limit = $clearLimit ? null : self::limit($limit);
        }
        $member->save();

        return $member;
    }

    public static function remove(BusinessMember $owner, BusinessMember $member, ?string $pin): void
    {
        self::authorize($owner, 'team');
        TransactionPin::check($owner->user, $pin);
        if ($member->role === 'owner' && self::owners($member->company_id) === 1) {
            throw ValidationException::withMessages(['member' => 'A business needs an owner. Make someone else owner first.']);
        }
        $member->delete();
    }

    public static function present(BusinessMember $m, ?BusinessMember $viewer = null): array
    {
        $limit = in_array($m->role, ['owner', 'checker'], true) ? self::approvalLimit($m) : null;

        return ['id' => $m->id, 'name' => $m->user?->name, 'handle' => $m->user ? Handle::for($m->user) : null, 'role' => $m->role,
            'approval_limit' => $limit !== null ? Money::format($limit) : null, 'you' => $viewer && $viewer->id === $m->id];
    }

    private static function owners(int $companyId): int
    {
        return BusinessMember::where('company_id', $companyId)->where('role', 'owner')->count();
    }

    private static function role(string $role): void
    {
        if (! in_array($role, self::ROLES, true)) {
            throw ValidationException::withMessages(['role' => 'Choose owner, maker, checker or viewer.']);
        }
    }

    private static function limit(?string $limit): ?int
    {
        if ($limit === null || $limit === '') {
            return null;
        }
        try {
            $cents = Money::cents($limit);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['approval_limit' => 'Write the limit in shillings, e.g. 1000000.']);
        }
        if ($cents <= 0) {
            throw ValidationException::withMessages(['approval_limit' => 'The limit must be more than zero.']);
        }

        return $cents;
    }

    private static function find(string $who): ?User
    {
        $who = trim($who);
        $u = match (true) {
            str_contains($who, '@') && ! str_starts_with($who, '@') => User::where('email', strtolower($who))->first(),
            str_starts_with($who, '@') || ! preg_match('/\d{6,}/', $who) => Handle::find($who),
            default => User::where('phone', Identity::normalisePhone($who))->first(),
        };

        return $u && ! $u->is_business_wallet ? $u : null;
    }
}
