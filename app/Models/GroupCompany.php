<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A business Sharet owns and runs (Website › Companies; public at /companies/{slug}). */
class GroupCompany extends Model
{
    protected $fillable = ['name', 'slug', 'sector', 'tagline', 'description', 'services', 'served', 'url', 'email', 'phone', 'logo', 'sort', 'published'];

    protected $casts = ['services' => 'array', 'published' => 'boolean'];

    public function scopeListed(Builder $q): Builder
    {
        return $q->where('published', true)->orderBy('sort')->orderBy('name');
    }

    public function getHostAttribute(): ?string
    {
        return $this->url ? rtrim(preg_replace('#^https?://#', '', $this->url), '/') : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }
}
