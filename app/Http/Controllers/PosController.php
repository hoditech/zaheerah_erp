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

use function Termwind\render;

class PosController extends Controller
{
    public function index()
    {
        // Get the current user's category_id
        $categoryId = auth()->user()->category_id;
    
        // Check if the user's category_id is 1
        $products = Product::with('category')
            ->when($categoryId != 1, function ($query) use ($categoryId) {
                // Filter by category_id if it is not 1
                $query->where('category_id', $categoryId);
            })
            ->get();
    
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
            // Create the sale record
            $sale = Sale::create(['total_amount' => $validated['total_amount']]);

            foreach ($validated['items'] as $item) {
                // Fetch the product
                $product = Product::find($item['product_id']);

                // Check if enough stock is available
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product ID {$item['product_id']}");
                }

                // Deduct stock
                $product->decrement('stock', $item['quantity']);

                // Create the sale item record
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Log the activity of stock deduction
                log_activity('Stock Deduction', 'Deducted ' . $item['quantity'] . ' of ' . $product->name . ' from stock.');
            }

            // Log the activity of sale creation
            log_activity('Sale Created', 'Sale created with ID: ' . $sale->id . ' for total amount: ' . $sale->total_amount);

            DB::commit();

            return response()->json([
                'message' => 'Sale completed successfully',
                'sale_id' => $sale->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the sale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateReceipt(Request $request)
    {
        $data = $request->all();

        $receiptData = [
            'logo' => asset('images/your-logo.png'), // Update with your actual logo path
            'businessName' => 'Your Business Name',
            'businessAddress' => '123 Business St, Lagos, Nigeria',
            'items' => $data['items'], // Array of items with name, price, quantity
            'total' => $data['total_amount'],
        ];

        $pdf = PDF::loadView('receipt', $receiptData);

        // Log the activity of receipt generation
        log_activity('Receipt Generated', 'Generated receipt for sale with total amount: ' . $data['total_amount']);

        return $pdf->stream('receipt.pdf'); 
    }
}