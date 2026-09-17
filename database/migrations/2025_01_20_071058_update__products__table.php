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
        Schema::table('products', function (Blueprint $table) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('price');
                $table->decimal('weight_per_unit', 8, 2)->nullable()->after('unit_cost');
                //$table->decimal('total_cost', 10, 2)->nullable()->after('weight_per_unit');
                // $table->integer('stock_receive')->nullable()->after('total_cost');
                //$table->integer('stock_release')->nullable()->after('stock_receive');
                //$table->integer('remaining_units')->nullable()->after('stock_release');
                $table->string('supplier_id')->nullable()->after('weight_per_unit');  // Adjust size if needed
                $table->date('exp_date')->nullable()->after('supplier');
                $table->string('batch_number')->nullable()->after('exp_date');  // Adjust size if needed
                $table->string('storage_location')->nullable()->after('batch_number');  // Adjust size if needed
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
