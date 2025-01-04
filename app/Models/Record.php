<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Record extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'data' => 'array',
        'date' => 'date:Y-m-d',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope to filter by type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Method to get specific fields from the data column
    public function getCustomField($key)
    {
        return $this->data[$key] ?? null;
    }
}
