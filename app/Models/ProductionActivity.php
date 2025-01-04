<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'batch_id',
        'activity_details',
        'logged_at',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
