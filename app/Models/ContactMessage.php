<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['audience', 'name', 'email', 'phone', 'business', 'country', 'brief', 'thanked_at', 'notified_at'];

    protected $casts = ['thanked_at' => 'datetime', 'notified_at' => 'datetime'];
}
