<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\SequentialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * The receiving details printed on every receipt.
     */
    private const STORE_INFO = [
        'address' => 'No 144 Allasawa Daza Housing Estate Gusau Zamfara State',
        'contact' => 'contact@zaheerah.com',
        'phone' => '+234 806 2242 794',
    ];

    public function index()
    {
        $user = auth()->user();

        $products = Product::with('category')
            ->when(! $user->seesAllCategories(), function ($query) use ($user) {
                $query->where('category_id', $user->category_id);
            })
            ->get();

        return Inertia::render('POS/Index', [
            'products' => $products,
        ]);
    }

    public function makeSale(Request $request)
    {
        // Prices are NOT accepted from the client: they are looked up from the
        // catalogue, so a tampered request cannot under-ring a sale.
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $lines = [];
            $total = 0.0;

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ((int) $product->stock < $quantity) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name} (Available: {$product->stock})",
                    ], 400);
                }

                $price = (float) $product->price;
                $total += $price * $quantity;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $sale = Sale::create([
                'total_amount' => number_format($total, 2, '.', ''),
                'receipt_number' => SequentialNumber::receipt(),
                'transaction_id' => Str::upper(Str::random(12)),
            ]);

            $saleItems = [];

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $quantity = $line['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name, // Capture current product name
                    'quantity' => $quantity,
                    'price' => $line['price'],
                ]);

                // Deduct first, so the log reports the stock that is actually left.
                $product->decrement('stock', $quantity);

                // Keep the inventory ledger in step with the stock column. POS
                // sales used to reduce stock without recording the movement, so
                // the ledger could never be reconciled against the product row.
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'quantity' => -$quantity,
                    'unit_cost' => (float) ($product->unit_cost ?? 0),
                    'type' => 'deduction',
                    'notes' => 'POS sale ' . $sale->receipt_number,
                ]);

                log_activity(
                    'Stock Deduction',
                    "Deducted {$quantity} units of {$product->name}. Remaining stock: {$product->stock}",
                );

                $saleItems[] = [
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $line['price'],
                ];
            }

            log_activity(
                'Sale Completed',
                "Sale #{$sale->receipt_number} recorded for ₦" . number_format($sale->total_amount, 2),
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'receipt_data' => [
                    'receipt_number' => $sale->receipt_number,
                    'transaction_id' => $sale->transaction_id,
                    'date' => $sale->created_at->format('M j, Y H:i:s'),
                    'items' => $saleItems,
                    'total_amount' => $sale->total_amount,
                    'store_info' => self::STORE_INFO,
                ],
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sale Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateReceipt(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'receipt_number' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $items = $validated['items'];
        $total = (float) ($validated['total_amount']
            ?? collect($items)->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']));

        try {
            // Render the real data. This view used to be a hard-coded demo
            // invoice that ignored the request entirely.
            $receiptHtml = view('receipt', [
                'items' => $items,
                'total' => $total,
                'receiptNumber' => $validated['receipt_number'] ?? null,
                'paymentMethod' => $validated['payment_method'] ?? 'cash',
                'date' => now(),
                'store' => self::STORE_INFO,
            ])->render();

            return response($receiptHtml, 200)->header('Content-Type', 'text/html');
        } catch (\Throwable $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error generating receipt',
            ], 500);
        }
    }

    /**
     * Get the stock of a specific product.
     */
    public function getStock(Product $product)
    {
        return response()->json([
            'product_id' => $product->id,
            'stock' => $product->stock,
        ]);
    }

    public function getReceipt($receiptNumber)
    {
        try {
            $cleanReceiptNumber = urldecode(trim($receiptNumber));

            $sale = Sale::with('items')
                ->where('receipt_number', $cleanReceiptNumber)
                ->firstOrFail();

            // Map items to match makeSale's structure
            $receiptItems = $sale->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name ?? 'Product Name Not Available',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Receipt retrieved successfully',
                'receipt_data' => [
                    'receipt_number' => $sale->receipt_number,
                    'transaction_id' => $sale->transaction_id,
                    'date' => $sale->created_at->format('M j, Y H:i:s'),
                    'payment_method' => 'cash',
                    'items' => $receiptItems,
                    'total_amount' => (float) $sale->total_amount,
                    'store_info' => self::STORE_INFO,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Receipt lookup failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Receipt not found',
            ], 404);
        }
    }

    public function saleHistory()
    {
        $sales = Sale::withCount('items')->latest()->get()->map(function ($sale) {
            return [
                'receipt_number' => $sale->receipt_number,
                'date' => $sale->created_at,
                'total_amount' => $sale->total_amount,
                'items_count' => $sale->items_count,
            ];
        });

        return response()->json($sales);
    }
}
