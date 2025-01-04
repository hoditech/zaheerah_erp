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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('type'); // Type of record, e.g., 'harvest', 'inventory_update', 'sales_entry'
            $table->json('data')->nullable(); // JSON field to store variable data for different record types
            $table->text('description')->nullable(); // Description for general notes
            $table->date('date'); // Date of the record entry
            $table->timestamps();
            $table->softDeletes(); // Soft delete to allow archiving records
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
