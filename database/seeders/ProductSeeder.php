<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * The real Zaheerah catalogue: the grains and commodities traded in bulk,
 * imported from the storefront so a fresh ERP install starts with everything.
 *
 * Each entry carries its image file from database/seeders/images/products/,
 * which is copied onto the public disk so a seeded product is self-contained
 * rather than depending on the repository.
 *
 * STOCK IS SEEDED AS 0 ON PURPOSE. The opening quantity is a real business
 * figure and a seeder should not invent one. Enter the real stock in the admin
 * (Products, then edit) and the shop picks it up on the next sync. Until then
 * these products read as "Out of Stock" on the website.
 */
class ProductSeeder extends Seeder
{
    /**
     * Seeded products keep the storefront's own category name so the shop's
     * "Grains" filter label keeps working.
     */
    private const CATEGORY = 'Food Setup';

    private const CATEGORY_DESCRIPTION = 'Grains and commodities traded in bulk';

    private const IMAGE_SOURCE = 'seeders/images/products';

    private const IMAGE_DESTINATION = 'products';

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(
            ['name' => self::CATEGORY],
            ['description' => self::CATEGORY_DESCRIPTION],
        )->id;

        foreach ($this->products() as $data) {
            // Publish the file first, so even a re-run repairs a missing image.
            $imageUrl = $this->publishImage($data['image']);

            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                [
                    'price' => $data['price'],
                    'unit_cost' => null,
                    'stock' => 0,
                    'category_id' => $categoryId,
                    'image_url' => $imageUrl,
                ],
            );

            // Opening stock is only logged once, and only when there is some.
            if ($product->wasRecentlyCreated && $product->stock > 0) {
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'quantity' => $product->stock,
                    'unit_cost' => $product->unit_cost ?? 0,
                    'type' => 'initial',
                    'batch_reference' => $product->batch_number ?? 'SEED-' . $product->id,
                    'notes' => 'Opening stock from the product seeder',
                ]);
            }
        }
    }

    /**
     * Copy a seed image onto the public disk.
     *
     * @return string|null the value to store in image_url, or null if missing
     */
    private function publishImage(string $file): ?string
    {
        $source = database_path(self::IMAGE_SOURCE . '/' . $file);

        if (! File::exists($source)) {
            return null;
        }

        $destination = self::IMAGE_DESTINATION . '/' . $file;

        if (! Storage::disk('public')->exists($destination)) {
            Storage::disk('public')->put($destination, File::get($source));
        }

        return 'storage/' . $destination;
    }

    /**
     * @return array<int, array{name: string, price: float, image: string}>
     */
    private function products(): array
    {
        return [
            ['name' => 'Hibiscus - Zobo (gari)', 'price' => 18000.0, 'image' => '1152-kgoh.jpg'],
            ['name' => 'Black Eye Beans (100kg/bag)', 'price' => 92000.0, 'image' => '1150-WhatsApp-Image-2024-12-05-at-13.42.jpg'],
            ['name' => 'White Sorghum (100kg/bag)', 'price' => 125000.0, 'image' => '1148-gibh.jpg'],
            ['name' => 'Tuwo Rice (100kg/bag)', 'price' => 125000.0, 'image' => '1078-WhatsApp-Image-2024-11-28-at-14.56.jpg'],
            ['name' => 'Rice (100kg/bag)', 'price' => 92000.0, 'image' => '1076-rice.jpg'],
            ['name' => 'Vegetable Oil', 'price' => 80000.0, 'image' => '1074-oil.jpg'],
            ['name' => 'Sugar (100kg/bag)', 'price' => 92000.0, 'image' => '1071-suga.jpg'],
            ['name' => 'Gerlic (100kg/bag)', 'price' => 92000.0, 'image' => '1069-gerlic.jpg'],
            ['name' => 'Palm Oil', 'price' => 60000.0, 'image' => '1067-oil2.jpg'],
            ['name' => 'Groundnuts(100kg/bag)', 'price' => 92000.0, 'image' => '1064-nuts.jpg'],
            ['name' => 'Ginger (100kg/bag)', 'price' => 92000.0, 'image' => '1061-ginger.jpg'],
            ['name' => 'Soya Beans (100kg/bag)', 'price' => 105000.0, 'image' => '1055-soya.jpg'],
            ['name' => 'Melon Seed - Egusi (100kg/bag)', 'price' => 92000.0, 'image' => '1052-melon-1.jpg'],
            ['name' => 'Dry Papper (100kg/bag)', 'price' => 10000.0, 'image' => '1050-papp.jpg'],
            ['name' => 'Sesame Seed - Ridi (100kg/bag)', 'price' => 92000.0, 'image' => '1047-ridi.jpg'],
            ['name' => 'Rice -Unhusked (100kg/bag)', 'price' => 92000.0, 'image' => '1045-rice-unh.jpg'],
            ['name' => 'Hibiscus - Zobo (100kg/bag)', 'price' => 18000.0, 'image' => '1042-zobo.jpg'],
            ['name' => 'Dry Okra (100kg/bag)', 'price' => 92000.0, 'image' => '1041-okra.jpg'],
            ['name' => 'Wheat (100kg/bag)', 'price' => 92000.0, 'image' => '1039-wheat.jpg'],
            ['name' => 'Brown Beans (100kg/bag)', 'price' => 92000.0, 'image' => '1037-brownbeans.jpg'],
            ['name' => 'Onion (100kg/bag)', 'price' => 170000.0, 'image' => '1780698253-21-WhatsApp_Image_2026-06-05_at_1.42.55_PM.jpeg'],
            ['name' => 'Red Sorghum (100kg/bag)', 'price' => 70000.0, 'image' => '1029-WhatsApp-Image-2024-11-28-at-14.56.jpg'],
            ['name' => 'Yellow Sorghum (100kg/bag)', 'price' => 70000.0, 'image' => '1017-yellowsg.jpg'],
            ['name' => 'White Beans (100kg/bag)', 'price' => 92000.0, 'image' => '1014-WhatsApp-Image-2024-11-28-at-14.56.18_183c8eef-e1732896177827.jpg'],
            ['name' => 'Pearl Millet - Maiwa (100kg/bag)', 'price' => 65000.0, 'image' => '1012-ldfhpgff.jpg'],
            ['name' => 'Browntop Millet - Gero (100kg/bag)', 'price' => 68000.0, 'image' => '1009-brownt.jpg'],
            ['name' => 'White Maize (100kg/bag)', 'price' => 70000.0, 'image' => '997-white-maize.jpg'],
            ['name' => 'Yellow Maize (100kg/bag)', 'price' => 70000.0, 'image' => '529-yellowmaize.jpg'],
        ];
    }
}
