<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'category_id',
        'total_cost',
        'order_date',
        'delivery_status',
    ];

    // Relationship to Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
