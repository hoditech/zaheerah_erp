<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Online storefront orders arrive through the API and are stored as ordinary
     * sales, so they show up in the existing reports alongside POS sales.
     * external_ref holds the storefront order number and is what makes the
     * intake idempotent when Paystack retries a callback.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('external_ref')->nullable()->unique()->after('transaction_id');
            $table->string('channel')->default('pos')->after('external_ref');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['external_ref']);
            $table->dropColumn([
                'external_ref',
                'channel',
                'customer_name',
                'customer_email',
                'customer_phone',
                'customer_address',
                'notes',
            ]);
        });
    }
};
