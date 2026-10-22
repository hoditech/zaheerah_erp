<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'user_id',
            'action',
            'description',
            'ip_address',
            'user_agent'
        ];

    public function user()
    {
        // Soft-deleted staff should still be named in the audit trail.
        return $this->belongsTo(User::class)->withTrashed();

    }
}
