<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A person on the team shown on /company (Website › Team). */
class TeamMember extends Model
{
    protected $fillable = ['name', 'role', 'bio', 'photo', 'linkedin', 'sort', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
    }
}
