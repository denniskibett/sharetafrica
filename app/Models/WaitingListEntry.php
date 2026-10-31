<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'intent', 'source', 'meta', 'status',
        'invited_by', 'invited_at', 'registered_at',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
        'invited_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}