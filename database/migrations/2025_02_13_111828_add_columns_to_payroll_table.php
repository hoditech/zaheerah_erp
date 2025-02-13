<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('location')->nullable(); // Location
            $table->string('designation')->nullable(); // Designation
            $table->decimal('working_days', 10, 2)->nullable(); // Working days
            $table->string('bank')->nullable(); // Bank name
            $table->string('account_number')->nullable(); // Account number
            $table->decimal('transport', 10, 2)->default(0); // Transport allowance
            $table->decimal('house_rent', 10, 2)->default(0); // House rent allowance
            $table->decimal('medicals', 10, 2)->default(0); // Medical allowance
            $table->decimal('others', 10, 2)->default(0); // Other allowances
            $table->decimal('gross_salary', 10, 2)->nullable(); // Gross salary
            $table->decimal('income_tax', 10, 2)->default(0); // Income tax
            $table->decimal('cra', 10, 2)->default(0); // CRA deduction
            $table->decimal('pension', 10, 2)->default(0); // Pension deduction
            $table->decimal('nhf', 10, 2)->default(0); // NHF deduction
            $table->decimal('nhis', 10, 2)->default(0); // NHIS deduction
            $table->decimal('salary_advance', 10, 2)->default(0); // Salary advance
            $table->decimal('total_deduction', 10, 2)->nullable(); // Total deduction
            $table->decimal('net_salary_only_tax', 10, 2)->nullable(); // Net salary with onl
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Drop the columns if needed
            $table->dropColumn([
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
            ]);
        });
    

    }
};
