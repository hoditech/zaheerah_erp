<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a paid storefront order into a real ERP sale.
 *
 * Design notes:
 *  - Idempotent on external_ref. Paystack can call the callback more than once,
 *    so a repeat for the same order number returns the original receipt rather
 *    than booking a second sale.
 *  - The customer has already paid. That means an unmatched product or a stock
 *    shortage must never reject the order; it is recorded and reported back in
 *    `warnings` so staff can clean it up.
 *  - Stock is never driven below zero, because the ERP's reports assume that.
 */
class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_ref' => 'required|string|max:255',
            'customer.name' => 'nullable|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.phone' => 'nullable|string|max:50',
            'customer.address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'paid_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_id' => 'nullable|integer',
        ]);

        // Replay of an order we have already booked.
        if ($existing = Sale::where('external_ref', $validated['external_ref'])->first()) {
            return response()->json([
                'message' => 'Order already recorded.',
                'duplicate' => true,
                'data' => $this->salePayload($existing),
            ], 200);
        }

        // The server totals the order itself; never trust a client-side amount.
        $total = collect($validated['items'])
            ->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);

        $warnings = [];

        try {
            $sale = DB::transaction(function () use ($validated, $total, &$warnings) {
                $sale = Sale::create([
                    'external_ref' => $validated['external_ref'],
                    'channel' => 'web',
                    'total_amount' => number_format($total, 2, '.', ''),
                    'receipt_number' => 'RC-' . str_pad((string) (Sale::max('id') + 1), 6, '0', STR_PAD_LEFT),
                    'transaction_id' => 'WEB-' . Str::upper(Str::random(10)),
                    'customer_name' => $validated['customer']['name'] ?? null,
                    'customer_email' => $validated['customer']['email'] ?? null,
                    'customer_phone' => $validated['customer']['phone'] ?? null,
                    'customer_address' => $validated['customer']['address'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $quantity = (int) $item['quantity'];
                    $product = $this->resolveProduct($item, $warnings);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $item['price'],
                    ]);

                    $this->applyStockMovement($product, $quantity, $warnings);
                }

                return $sale;
            });
        } catch (Throwable $e) {
            Log::error('Storefront order intake failed', [
                'external_ref' => $validated['external_ref'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not record the order.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $this->log($validated, $sale, $warnings);

        return response()->json([
            'message' => 'Order recorded.',
            'duplicate' => false,
            'warnings' => $warnings,
            'data' => $this->salePayload($sale),
        ], 201);
    }

    /**
     * Match a storefront line to an ERP product, by ERP id first and by name
     * second. An unknown product is created rather than dropped, because the
     * customer has already paid for it.
     */
    private function resolveProduct(array $item, array &$warnings): Product
    {
        if (! empty($item['product_id'])) {
            if ($product = Product::find($item['product_id'])) {
                return $product;
            }
        }

        $product = Product::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($item['name']))])->first();

        if ($product) {
            return $product;
        }

        $product = Product::create([
            'name' => $item['name'],
            'price' => $item['price'],
            'stock' => 0,
            // Resolve the fallback category by name rather than a magic id, which
            // would fail the foreign key on a freshly seeded database.
            'category_id' => Category::query()->firstOrCreate(
                ['name' => 'General'],
                ['description' => 'Users not specific to any category'],
            )->id,
        ]);

        $warnings[] = sprintf(
            'Product "%s" was not in the ERP catalogue and was created automatically (id %d, category "General"). Please review its category, cost and stock.',
            $product->name,
            $product->id
        );

        return $product;
    }

    /**
     * Deduct sold units, clamped at zero so stock never goes negative.
     */
    private function applyStockMovement(Product $product, int $quantity, array &$warnings): void
    {
        if ($product->stock >= $quantity) {
            $product->decrement('stock', $quantity);

            return;
        }

        $available = (int) $product->stock;

        if ($available > 0) {
            $product->update(['stock' => 0]);
        }

        $warnings[] = sprintf(
            'Insufficient stock for "%s": sold %d but only %d was available. Stock was set to 0 - please reconcile.',
            $product->name,
            $quantity,
            $available
        );
    }

    private function salePayload(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'external_ref' => $sale->external_ref,
            'receipt_number' => $sale->receipt_number,
            'transaction_id' => $sale->transaction_id,
            'channel' => $sale->channel,
            'total_amount' => (float) $sale->total_amount,
            'created_at' => $sale->created_at?->toIso8601String(),
        ];
    }

    private function log(array $validated, Sale $sale, array $warnings): void
    {
        // Logging is a side concern: never let it break a recorded sale.
        try {
            $description = sprintf(
                'Online order %s recorded as sale %s for NGN %s',
                $validated['external_ref'],
                $sale->receipt_number,
                number_format((float) $sale->total_amount, 2)
            );

            if ($warnings) {
                $description .= ' | ' . implode(' | ', $warnings);
            }

            log_activity('Online Order', $description);
        } catch (Throwable $e) {
            Log::warning('Could not write activity log for storefront order', [
                'external_ref' => $validated['external_ref'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
