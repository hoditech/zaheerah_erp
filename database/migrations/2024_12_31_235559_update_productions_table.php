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
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('material_cost', 10, 2)->default(0)->after('quantity');
            $table->decimal('labor_cost', 10, 2)->default(0)->after('material_cost');
            $table->decimal('overhead_cost', 10, 2)->default(0)->after('labor_cost');
            $table->decimal('total_cost', 10, 2)->storedAs('material_cost + labor_cost + overhead_cost')->after('overhead_cost');
            $table->string('batch_status')->default('pending')->after('production_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            //
        });
    }
};
