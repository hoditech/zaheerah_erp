<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Reference to the employees table
            $table->foreignId('batch_id')->constrained('production_batches')->onDelete('cascade'); // Reference to production batches
            $table->text('activity_details'); // Description of the activity
            $table->dateTime('logged_at')->default(now()); // Timestamp for activity log
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_activities');
    }
};
