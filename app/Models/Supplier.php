<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'address',
        'category_id',
        'status',
    ];

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
