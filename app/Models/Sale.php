<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_amount',
        'receipt_number',
        'transaction_id',
        'external_ref',
        'channel',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}


