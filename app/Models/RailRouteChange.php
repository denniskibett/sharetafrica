<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A proposed change to the rail switch: one person proposes, a different person approves with their password. */
class RailRouteChange extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['decided_at' => 'datetime'];

    public function proposer()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
