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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->decimal('base_salary', 10, 2)->nullable(); 
            $table->decimal('overtime_pay', 10, 2)->default(0); 
            $table->decimal('bonuses', 10, 2)->default(0); 
            $table->decimal('deductions', 10, 2)->default(0); 
            $table->decimal('tax', 10, 2)->default(0); 
            $table->decimal('commission', 10, 2)->default(0); 
            $table->decimal('net_salary', 10, 2)->nullable(); 
            $table->enum('payment_method', ['bank_transfer', 'cash', 'mobile_money'])->default('bank_transfer'); 
            $table->enum('payment_status', ['pending', 'paid'])->default('pending'); 
            $table->date('payment_date')->nullable(); 
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
