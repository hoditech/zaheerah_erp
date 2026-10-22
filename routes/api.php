<?php

use App\Http\Controllers\Api\BloomContentController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront API
|--------------------------------------------------------------------------
|
| Server-to-server endpoints used by the public Zaheerah storefront.
| Every route requires the shared secret in the X-Storefront-Key header,
| which is configured as STOREFRONT_API_KEY in .env on both applications.
|
| These routes carry no session and no Sanctum token: the storefront is a
| separate application, not a logged-in user of the ERP.
|
*/

Route::middleware('storefront')->prefix('v1')->group(function () {
    Route::get('/ping', fn () => response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
    ]));

    // Catalogue: ERP is the source of truth for name, price and stock.
    Route::get('/catalog/products', [CatalogController::class, 'index']);
    Route::post('/catalog/products', [CatalogController::class, 'upsert']);

    // Bloom page content: tier benefits and featured carton cards.
    Route::get('/catalog/bloom-tiers', [BloomContentController::class, 'tiers']);
    Route::get('/catalog/bloom-products', [BloomContentController::class, 'featuredProducts']);

    // Paid storefront orders become ERP sales and reduce stock.
    Route::post('/orders', [OrderController::class, 'store']);

    // Quote requests and contact messages.
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads', [LeadController::class, 'index']);
});
