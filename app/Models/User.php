<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\Customer;
use App\Models\Company;
use App\Models\WaitingListEntry;
use App\Models\Enquiry;
use App\Models\DeveloperApplication;
use App\Models\TradeApplication;

class User extends Authenticatable implements Wallet, Customer
{
    use HasFactory, Notifiable, SoftDeletes;
    use HasRoles;
    use HasWallet, CanPay;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar',
        'bio',

        'country',
        'city',
        'state',
        'postal_code',
        'tax_id',
        'company_id',

        'intent',
        'onboarding_status',
        'invited_at',
        'onboarded_at',
        'invited_by',

        'status',
        'verified_by',

        'social',
        'email_verified_at',
    ];

    /**
     * Hidden from serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'invited_at' => 'datetime',
        'onboarded_at' => 'datetime',
        'social' => 'array',
        'status' => 'boolean',
    ];

    // ==========================================================
    // RELATIONSHIPS
    // ==========================================================

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invitees()
    {
        return $this->hasMany(User::class, 'invited_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ==========================================================
    // ACCESSORS
    // ==========================================================

    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->full_name;
        $words = array_filter(explode(' ', $name));
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper($word[0]);
        }
        return substr($initials, 0, 2);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&background=D4FF3D&color=0A0A0C&size=128";
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company?->name;
    }

    public function belongsToCompany(): bool
    {
        return !is_null($this->company_id);
    }

    // ==========================================================
    // SPATIE — ROLE HELPERS
    // ==========================================================

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function isOnWaitingList(): bool
    {
        return $this->onboarding_status === 'waiting_list';
    }

    public function isOnboarded(): bool
    {
        return $this->onboarding_status === 'active';
    }

    public function needsOnboarding(): bool
    {
        return in_array($this->onboarding_status, ['waiting_list', 'invited', 'in_progress']);
    }

    public function isIndividual(): bool
    {
        return $this->intent === 'individual';
    }

    public function isMerchant(): bool
    {
        return $this->intent === 'merchant';
    }

    public function isBusiness(): bool
    {
        return $this->intent === 'business';
    }

    public function isTechie(): bool
    {
        return $this->intent === 'techie';
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    public function isSupport(): bool
    {
        return $this->hasRole('support');
    }

    public function isOperations(): bool
    {
        return $this->hasRole('operations');
    }

    // ==========================================================
    // ONBOARDING — WAITING LIST FLOW
    // ==========================================================

    public function invite(?User $by = null): void
    {
        $this->update([
            'onboarding_status' => 'invited',
            'invited_at' => now(),
            'invited_by' => $by?->id,
        ]);
    }

    public function pivotTo(string $intent, string $role): void
    {
        $this->update([
            'intent' => $intent,
            'onboarding_status' => 'active',
            'onboarded_at' => now(),
        ]);

        $this->syncRoles([$role]);
    }

    // ==========================================================
    // ROUTING — HOME PER ROLE
    // ==========================================================

    public function homeRoute(): string
    {
        if ($this->hasAnyRole(['super_admin', 'admin'])) {
            return route('dashboard');
        }

        if ($this->hasAnyRole(['support', 'operations'])) {
            return route('dashboard');
        }

        if ($this->isOnWaitingList() || $this->needsOnboarding()) {
            return route('onboarding.welcome');
        }

        return match ($this->intent) {
            'merchant' => route('merchant.dashboard'),
            'business' => route('business.dashboard'),
            'techie' => route('developer.dashboard'),
            'individual' => route('individual.dashboard'),
            default => route('dashboard'),
        };
    }

    // ==========================================================
    // WALLET HELPERS
    // ==========================================================

    public function getOrCreateWallet(?string $name = null): Wallet
    {
        $wallet = $this->wallet;

        if ($wallet) {
            return $wallet;
        }

        return $this->createWallet([
            'name' => $name ?? ($this->name . "'s Wallet"),
        ]);
    }

    public function getWalletBalance(): float
    {
        return (float) $this->balance;
    }

    public function getFormattedWalletBalance(): string
    {
        return 'KES ' . number_format($this->getWalletBalance(), 2);
    }

    // Inside app/Models/User.php, in the RELATIONSHIPS section:

    public function waitingListEntry()
    {
        return $this->hasOne(WaitingListEntry::class);
    }

    public function waitingListEntries()
    {
        return $this->hasMany(WaitingListEntry::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    public function developerApplications()
    {
        return $this->hasMany(DeveloperApplication::class);
    }

    public function tradeApplications()
    {
        return $this->hasMany(TradeApplication::class);
    }
}