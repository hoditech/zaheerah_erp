<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'unit_cost',
        'type', 
        'batch_reference',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
