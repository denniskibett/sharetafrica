<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model implements Wallet
{
    use HasFactory, SoftDeletes;
    use HasWallet; // company wallet: merchant/business balance (bavix)

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'legal_name',
        'slug',
        'email',
        'phone',
        'website',
        'logo',
        'type',
        'registration_number',
        'tax_id',
        'country',
        'city',
        'address',
        'status',
        'verified_at',
        'verified_by',
        'settings',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'status' => 'boolean',
        'verified_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Automatically generate a slug when creating if none provided.
     */
    protected static function booted(): void
    {
        // A company's wallet follows its verification: business limits once verified, none before
        static::updated(function (Company $company) {
            if ($company->wasChanged('verified_at') && $company->wallet_user_id) {
                User::whereKey($company->wallet_user_id)->first()?->forceFill(['kyc_level' => $company->verified_at ? 'business' : 'none'])->save();
            }
        });
        static::creating(function (Company $company) {
            $company->sharet_id ??= \App\Support\SharetId::generate();   // fixed business account number (docs/v2/INTEGRATION.md §1)
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    /**
     * The company's own Sharet account (docs/v2/INTEGRATION.md §1): the company's Sharet ID, an @name from its name, and
     * business limits once the company is verified. It can never sign in; API keys of the company act for it.
     */
    public function walletUser(): User
    {
        if ($this->wallet_user_id && ($u = User::find($this->wallet_user_id))) {
            return $u;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () {
            $base = trim(preg_replace('/[^a-z0-9]+/', '.', \Illuminate\Support\Str::ascii(strtolower($this->name ?: 'business'))), '.') ?: 'business';
            $base = \Illuminate\Support\Str::limit($base, 24, '');
            $handle = $base;
            for ($n = 2; User::where('handle', $handle)->exists(); $n++) {
                $handle = $base.$n;
            }
            $u = new User;
            $u->forceFill([
                'name' => $this->name,
                'email' => 'wallet+'.strtolower($this->sharet_id).'@business.sharet.africa',
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(64)),   // never used: wallets can't sign in
                'is_business_wallet' => true,
                'company_id' => $this->id,
                'sharet_id' => $this->sharet_id,
                'handle' => $handle,
                'kyc_level' => $this->verified_at ? 'business' : 'none',
            ])->save();
            $this->forceFill(['wallet_user_id' => $u->id])->saveQuietly();

            return $u;
        });
    }

    // ==========================================================
    // RELATIONSHIPS
    // ==========================================================

    /**
     * Users belonging to this company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * The user who verified this company.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ==========================================================
    // ACCESSORS
    // ==========================================================

    /**
     * Is the company verified?
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Full display name (legal name if present, else name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->legal_name ?: $this->name;
    }

    /**
     * Logo URL with fallback.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=D4FF3D&color=0A0A0C&size=128";
    }

    // ==========================================================
    // SCOPES
    // ==========================================================

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ==========================================================
    // HELPERS
    // ==========================================================

    /**
     * Verify the company.
     */
    public function verify(?User $by = null): void
    {
        $this->update([
            'verified_at' => now(),
            'verified_by' => $by?->id,
        ]);
    }

    /**
     * Check if a given user belongs to this company.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('id', $user->id)->exists();
    }
}