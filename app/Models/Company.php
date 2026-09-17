<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

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
        static::creating(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . Str::lower(Str::random(6));
            }
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