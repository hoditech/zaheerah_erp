<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'base_salary',
        'overtime_pay',
        'bonuses',
        'deductions',
        'tax',
        'commission',
        'net_salary',
        'payment_method',
        'payment_status',
        'payment_date',
    ];

    // Relationship with User (Employee)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateNetSalary()
    {
        return ($this->base_salary + $this->overtime_pay + $this->bonuses + $this->commission)
            - ($this->deductions + $this->tax);
    }
}
