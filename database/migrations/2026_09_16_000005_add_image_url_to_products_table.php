<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products are created in the ERP admin and mirrored to the shop, so the
     * image has to live here as the single source of truth.
     *
     * Stores the same shape the storefront already uses: a relative path such as
     * "storage/products/rice.jpg", or an absolute URL when a product was
     * imported from the shop rather than uploaded.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
