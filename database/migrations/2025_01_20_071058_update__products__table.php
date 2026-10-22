<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the catalogue columns.
     *
     * Portable `after()` targets only: the original version positioned a column
     * after "supplier", which does not exist, so a fresh migrate on MySQL would
     * fail (SQLite ignored it, hiding the problem).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->after('price');
            $table->decimal('weight_per_unit', 8, 2)->nullable()->after('unit_cost');
            $table->string('supplier_id')->nullable()->after('weight_per_unit');
            $table->date('exp_date')->nullable()->after('supplier_id');
            $table->string('batch_number')->nullable()->after('exp_date');
            $table->string('storage_location')->nullable()->after('batch_number');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'unit_cost',
                'weight_per_unit',
                'supplier_id',
                'exp_date',
                'batch_number',
                'storage_location',
            ]);
        });
    }
};
