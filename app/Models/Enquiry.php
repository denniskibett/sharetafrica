<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'audience',
        'business', 'role', 'brief', 'reference_url', 'meta',
        'status', 'handled_by', 'responded_at',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}