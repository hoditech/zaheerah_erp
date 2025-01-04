<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'quantity',
        'material_cost',
        'labor_cost',
        'overhead_cost',
        'total_cost',
        'batch_status',
        'description', 

    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
