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
        Schema::table('products', function (Blueprint $table) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('weight', 8, 2)->nullable()->after('existing_column');  // Adjust precision as needed
                $table->decimal('weight_per_bag', 8, 2)->nullable()->after('weight');
                $table->decimal('total_cost', 10, 2)->nullable()->after('weight_per_bag');
                $table->integer('stock_receive')->nullable()->after('total_cost');
                $table->integer('stock_release')->nullable()->after('stock_receive');
                $table->integer('remaining_bags')->nullable()->after('stock_release');
                $table->string('supplier')->nullable()->after('remaining_bags');  // Adjust size if needed
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
