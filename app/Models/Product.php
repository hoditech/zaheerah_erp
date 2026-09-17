<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'unit_cost',      
        'stock',          
        'category_id',    
        'image_url',
        'weight_per_unit',
        'supplier_id',    
        'exp_date',       
        'batch_number',   
        'storage_location',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->unit_cost * $this->stock;
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
