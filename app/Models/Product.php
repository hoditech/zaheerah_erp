<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    // New Relationship
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Access sales through sale items
    public function sales()
    {
        return $this->hasManyThrough(Sale::class, SaleItem::class, 'product_id', 'id', 'id', 'sale_id');
    }
}
