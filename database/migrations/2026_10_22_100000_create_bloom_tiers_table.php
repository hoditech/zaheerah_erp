<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The three "Bloom Tier Benefits" cards shown on the public Bloom page.
     *
     * These used to be hard-coded in the frontsite Blade template. The ERP is
     * now their source of truth; the frontsite mirrors them through
     * `erp:sync-bloom`.
     *
     * @var list<array<string, mixed>>
     */
    private array $defaults = [
        [
            'slug' => 'bloom-1',
            'name' => 'Bloom 1',
            'subtitle' => 'Retail',
            'icon' => '✨',
            'min_cartons' => 100,
            'max_cartons' => 199,
            'range_label' => null,
            'unit_label' => 'cartons',
            'price_per_carton' => null,
            'discount_percent' => null,
            'benefits' => ['Base wholesale pricing', 'Small outlet support', 'Standard delivery'],
            'cta_label' => 'Select Plan',
            'cta_url' => null,
            'footnote' => 'Ideal for small retail shops starting out.',
            'badge' => null,
            'theme' => 'cream',
            'is_active' => true,
            'sort_order' => 1,
        ],
        [
            'slug' => 'bloom-2',
            'name' => 'Bloom 2',
            'subtitle' => 'Retail',
            'icon' => '🌟',
            'min_cartons' => 200,
            'max_cartons' => 499,
            'range_label' => null,
            'unit_label' => 'cartons',
            'price_per_carton' => null,
            'discount_percent' => null,
            'benefits' => ['Discounted carton pricing', 'Priority stock access', 'Better profit margin'],
            'cta_label' => 'Select Plan',
            'cta_url' => null,
            'footnote' => 'Growing outlets and regional resellers.',
            'badge' => 'Most Popular',
            'theme' => 'silver',
            'is_active' => true,
            'sort_order' => 2,
        ],
        [
            'slug' => 'bloom-3',
            'name' => 'Bloom 3',
            'subtitle' => 'Retail',
            'icon' => '👑',
            'min_cartons' => 500,
            'max_cartons' => null,
            'range_label' => '500+',
            'unit_label' => 'cartons',
            'price_per_carton' => null,
            'discount_percent' => null,
            'benefits' => ['Factory-level pricing', 'Regional distribution access', 'Bulk fulfillment priority'],
            'cta_label' => 'Select Plan',
            'cta_url' => null,
            'footnote' => 'Large Retail',
            'badge' => null,
            'theme' => 'platinum',
            'is_active' => true,
            'sort_order' => 3,
        ],
    ];

    public function up(): void
    {
        Schema::create('bloom_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 60);
            $table->string('subtitle', 60)->nullable();
            $table->string('icon', 16)->nullable();
            $table->unsignedInteger('min_cartons')->nullable();
            $table->unsignedInteger('max_cartons')->nullable();
            $table->string('range_label', 40)->nullable();
            $table->string('unit_label', 20)->default('cartons');
            $table->decimal('price_per_carton', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->json('benefits')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->string('footnote', 255)->nullable();
            $table->string('badge', 40)->nullable();
            $table->string('theme', 20)->default('cream');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();

        foreach ($this->defaults as $tier) {
            DB::table('bloom_tiers')->insert([
                'slug' => $tier['slug'],
                'name' => $tier['name'],
                'subtitle' => $tier['subtitle'],
                'icon' => $tier['icon'],
                'min_cartons' => $tier['min_cartons'],
                'max_cartons' => $tier['max_cartons'],
                'range_label' => $tier['range_label'],
                'unit_label' => $tier['unit_label'],
                'price_per_carton' => $tier['price_per_carton'],
                'discount_percent' => $tier['discount_percent'],
                'benefits' => json_encode($tier['benefits']),
                'cta_label' => $tier['cta_label'],
                'cta_url' => $tier['cta_url'],
                'footnote' => $tier['footnote'],
                'badge' => $tier['badge'],
                'theme' => $tier['theme'],
                'is_active' => $tier['is_active'],
                'sort_order' => $tier['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bloom_tiers');
    }
};
