<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Best-Selling Cartons for Retailers" cards shown on the public Bloom
     * page. The ERP owns them and the frontsite mirrors them via
     * `erp:sync-bloom`.
     *
     * @var list<array<string, mixed>>
     */
    private array $defaults = [
        [
            'slug' => 'washing-soap-carton',
            'name' => 'Washing Soap (Laundry Bars)',
            'pack_description' => '48 bars per carton',
            'price_per_carton' => 12500,
            'retail_margin' => 6000,
            'badge' => null,
            'image_url' => null,
            'cta_label' => 'Add to Order',
            'cta_url' => null,
            'is_highlighted' => false,
            'is_active' => true,
            'sort_order' => 1,
        ],
        [
            'slug' => 'bathing-soap-carton',
            'name' => 'Bathing Soap (Beauty & Herbal)',
            'pack_description' => '72 tablets per carton',
            'price_per_carton' => 15800,
            'retail_margin' => 8500,
            'badge' => 'High Demand',
            'image_url' => null,
            'cta_label' => 'Add to Order',
            'cta_url' => null,
            'is_highlighted' => true,
            'is_active' => true,
            'sort_order' => 2,
        ],
        [
            'slug' => 'liquid-soap-carton',
            'name' => 'Liquid Soap (Multipurpose)',
            'pack_description' => '24 bottles (500ml) per carton',
            'price_per_carton' => 18200,
            'retail_margin' => 9800,
            'badge' => null,
            'image_url' => null,
            'cta_label' => 'Add to Order',
            'cta_url' => null,
            'is_highlighted' => false,
            'is_active' => true,
            'sort_order' => 3,
        ],
    ];

    public function up(): void
    {
        Schema::create('bloom_featured_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 120);
            $table->string('pack_description', 120)->nullable();
            $table->decimal('price_per_carton', 12, 2)->nullable();
            $table->decimal('retail_margin', 12, 2)->nullable();
            $table->string('badge', 40)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();

        foreach ($this->defaults as $product) {
            DB::table('bloom_featured_products')->insert([
                'slug' => $product['slug'],
                'name' => $product['name'],
                'pack_description' => $product['pack_description'],
                'price_per_carton' => $product['price_per_carton'],
                'retail_margin' => $product['retail_margin'],
                'badge' => $product['badge'],
                'image_url' => $product['image_url'],
                'cta_label' => $product['cta_label'],
                'cta_url' => $product['cta_url'],
                'is_highlighted' => $product['is_highlighted'],
                'is_active' => $product['is_active'],
                'sort_order' => $product['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bloom_featured_products');
    }
};
