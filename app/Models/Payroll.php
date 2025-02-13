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
        'location',
        'designation',
        'working_days',
        'bank',
        'account_number',
        'transport',
        'house_rent',
        'medicals',
        'others',
        'gross_salary',
        'income_tax',
        'cra',
        'pension',
        'nhf',
        'nhis',
        'salary_advance',
        'total_deduction',
        'net_salary_only_tax',
    ];

    // Relationship with User (Employee)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Automatically calculate gross_salary
    public function getGrossSalaryAttribute()
    {
        return $this->base_salary + $this->overtime_pay + $this->bonuses + $this->commission
            + $this->transport + $this->house_rent + $this->medicals + $this->others;
    }

    // Automatically calculate total_deduction
    public function getTotalDeductionAttribute()
    {
        return $this->deductions + $this->tax + $this->income_tax + $this->cra
            + $this->pension + $this->nhf + $this->nhis + $this->salary_advance;
    }

    // Automatically calculate net_salary
    public function getNetSalaryAttribute()
    {
        return $this->gross_salary - $this->total_deduction;
    }

    // Automatically calculate net_salary_only_tax
    public function getNetSalaryOnlyTaxAttribute()
    {
        return $this->gross_salary - $this->income_tax;
    }

    // Automatically set calculated fields when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Calculate and set gross_salary
            $model->gross_salary = $model->base_salary + $model->overtime_pay + $model->bonuses + $model->commission
                + $model->transport + $model->house_rent + $model->medicals + $model->others;

            // Calculate and set total_deduction
            $model->total_deduction = $model->deductions + $model->tax + $model->income_tax + $model->cra
                + $model->pension + $model->nhf + $model->nhis + $model->salary_advance;

            // Calculate and set net_salary
            $model->net_salary = $model->gross_salary - $model->total_deduction;

            // Calculate and set net_salary_only_tax
            $model->net_salary_only_tax = $model->gross_salary - $model->income_tax;
        });
    }
}