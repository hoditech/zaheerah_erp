<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloomFeaturedProduct;
use App\Models\BloomTier;
use Illuminate\Http\JsonResponse;

/**
 * Bloom page content for the public storefront.
 *
 * The frontsite mirrors these into its own tables through `erp:sync-bloom`, the
 * same way it mirrors the product catalogue. The ERP remains the source of
 * truth for every number and label shown on /zaheerah-bloom.
 */
class BloomContentController extends Controller
{
    /**
     * GET /api/v1/catalog/bloom-tiers
     */
    public function tiers(): JsonResponse
    {
        $tiers = BloomTier::active()
            ->ordered()
            ->get()
            ->map(fn (BloomTier $tier) => $tier->toStorefrontArray())
            ->values();

        return response()->json([
            'data' => $tiers,
            'meta' => [
                'count' => $tiers->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v1/catalog/bloom-products
     */
    public function featuredProducts(): JsonResponse
    {
        $products = BloomFeaturedProduct::active()
            ->ordered()
            ->get()
            ->map(fn (BloomFeaturedProduct $product) => $product->toStorefrontArray())
            ->values();

        return response()->json([
            'data' => $products,
            'meta' => [
                'count' => $products->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
