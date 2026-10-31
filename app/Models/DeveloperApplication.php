<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeveloperApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'interest', 'brief',
        'api_key', 'sandbox_key',
        'status', 'reviewed_by', 'reviewed_at', 'approved_at',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}