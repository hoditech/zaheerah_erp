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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to employees
            $table->decimal('base_salary', 10, 2)->nullable(); // Fixed salary
            $table->decimal('overtime_pay', 10, 2)->default(0); // Extra work pay
            $table->decimal('bonuses', 10, 2)->default(0); // Extra earnings
            $table->decimal('deductions', 10, 2)->default(0); // E.g., loans, penalties
            $table->decimal('tax', 10, 2)->default(0); // Taxation
            $table->decimal('commission', 10, 2)->default(0); // Commission-based earnings
            $table->decimal('net_salary', 10, 2)->nullable(); // Final amount after deductions
            $table->enum('payment_method', ['bank_transfer', 'cash', 'mobile_money'])->default('bank_transfer'); // Payment mode
            $table->enum('payment_status', ['pending', 'paid'])->default('pending'); // Payment status
            $table->date('payment_date')->nullable(); // Date of payment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
