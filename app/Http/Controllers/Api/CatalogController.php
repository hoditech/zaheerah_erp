<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read/write access to the product catalogue for the public storefront.
 *
 * The ERP is the single source of truth for what exists, what it costs and how
 * much is left. The storefront keeps its own marketing copy and imagery, and
 * matches rows through erp_product_id.
 */
class CatalogController extends Controller
{
    /**
     * GET /api/v1/catalog/products
     *
     * ?updated_since=ISO8601 limits the response for incremental syncs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->orderBy('id');

        if ($since = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $since);
        }

        $products = $query->get()->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            // Absolute so the storefront can use it directly. url() leaves an
            // already-absolute value (an image imported from the shop) alone.
            'image_url' => $product->image_url ? url($product->image_url) : null,
            'updated_at' => $product->updated_at?->toIso8601String(),
        ]);

        return response()->json([
            'data' => $products,
            'meta' => [
                'count' => $products->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/v1/catalog/products
     *
     * Creates or updates a product by name. Used once to import the products
     * that already exist on the storefront, so the ERP owns the whole catalogue.
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'external_ref' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $categoryId = null;

        if (! empty($validated['category'])) {
            $categoryId = Category::firstOrCreate(
                ['name' => $validated['category']],
                ['description' => 'Imported from the storefront catalogue']
            )->id;
        }

        $product = Product::firstOrNew(['name' => $validated['name']]);
        $created = ! $product->exists;

        $product->price = $validated['price'];

        if (array_key_exists('stock', $validated) && $validated['stock'] !== null) {
            $product->stock = $validated['stock'];
        } elseif ($created) {
            $product->stock = 0;
        }

        if (! empty($validated['unit_cost'])) {
            $product->unit_cost = $validated['unit_cost'];
        }

        if ($categoryId) {
            $product->category_id = $categoryId;
        }

        // Only set the image when one is supplied, so a shop-side sync cannot
        // wipe an image that was uploaded in the admin.
        if (! empty($validated['image_url'])) {
            $product->image_url = $validated['image_url'];
        }

        $product->save();

        // Opening stock is only ever logged once, when the product is born.
        if ($created && $product->stock > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'quantity' => $product->stock,
                'unit_cost' => $product->unit_cost ?? 0,
                'type' => 'initial',
                'batch_reference' => $product->batch_number ?? 'IMPORT-' . $product->id,
                'notes' => 'Opening stock imported from the storefront catalogue',
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'category' => $product->category?->name,
                'image_url' => $product->image_url ? url($product->image_url) : null,
                'created' => $created,
            ],
        ], $created ? 201 : 200);
    }
}
