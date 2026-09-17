<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\Debugbar\Facades\Debugbar;

use Str;
use function Termwind\render;

class PosController extends Controller
{
    public function index()
    {
        $categoryId = auth()->user()->category_id;

        $products = Product::with('category')
            ->when($categoryId != 1, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->get(); // Paginate with 10 items per page

        return Inertia::render("POS/Index", [
            'products' => $products,
        ]);
    }

    public function makeSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create the sale record with additional metadata
            $sale = Sale::create([
                'total_amount' => number_format($validated['total_amount'], 2, '.', ''),
                'receipt_number' => 'RC-' . str_pad(Sale::max('id') + 1, 6, '0', STR_PAD_LEFT),
                'transaction_id' => Str::upper(Str::random(12)),
            ]);

            $saleItems = [];
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    // Roll back before returning: bailing out here used to leave
                    // the transaction open, along with a half-written sale.
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name} (Available: {$product->stock})",
                    ], 400);
                }

                // Store product name at time of sale
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name, // Capture current product name
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Deduct first, so the log reports the stock that is actually left.
                $product->decrement('stock', $item['quantity']);

                log_activity(
                    'Stock Deduction',
                    "Deducted {$item['quantity']} units of {$product->name}. Remaining stock: {$product->stock}",
                );

                $saleItems[] = [
                    'name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
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
                    'store_info' => [
                        'address' => 'No 144 Allasawa Daza Housing Estate Gusau Zamfara State',
                        'contact' => 'contact@zaheerah.com',
                        'phone' => '+234 806 2242 794'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sale Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateReceipt(Request $request)
    {
        \Log::info('Request Data:', $request->all());

        $receiptData = [
            'items' => $request->items,
            'total' => $request->total_amount,
        ];

        try {
            // Generate the receipt HTML content instead of PDF
            $receiptHtml = view('receipt', $receiptData)->render();

            // Return the HTML content as the response
            return response($receiptHtml, 200)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'message' => 'Error generating receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get the stock of a specific product.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStock(Product $product)
    {
        // Return the stock information
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
                    'price' => (float) $item->price
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Receipt retrieved successfully',
                'receipt_data' => [
                    'receipt_number' => $sale->receipt_number,
                    'transaction_id' => $sale->transaction_id,
                    'date' => $sale->created_at->format('M j, Y H:i:s'),
                    'payment_method' => $sale->payment_method ?? 'cash',
                    'items' => $receiptItems,
                    'total_amount' => (float) $sale->total_amount,
                    'store_info' => [
                        'address' => 'No 144 Allasawa Daza Housing Estate Gusau Zamfara State',
                        'contact' => 'contact@zaheerah.com',
                        'phone' => '+234 806 2242 794'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error("Receipt lookup failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found'
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