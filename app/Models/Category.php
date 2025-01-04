<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    // Relationship with Product model
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    

}
