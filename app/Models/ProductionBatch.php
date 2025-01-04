<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'batch_number',
        'quantity',
        'production_date',
        'description', 

    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function activities()
    {
        return $this->hasMany(ProductionActivity::class, 'batch_id');
    }
}
