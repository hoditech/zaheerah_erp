<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineLead extends Model
{
    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'payload',
        'source',
        'external_ref',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
