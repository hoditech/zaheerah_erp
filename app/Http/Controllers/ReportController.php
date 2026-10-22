<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Log;

class ReportController extends Controller
{
    // Sales Report with date filtering (JSON response)
    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Sale::with(['items.product'])
            ->when($request->start_date, function ($q) use ($validated) {
                $q->where('created_at', '>=', $validated['start_date']);
            })
            ->when($request->end_date, function ($q) use ($validated) {
                $q->where('created_at', '<=', Carbon::parse($validated['end_date'])->endOfDay());
            });

        $totalSales = $query->sum('total_amount');
        $transactions = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_sales' => $totalSales,
                'transactions' => $transactions,
            ],
        ]);
    }

    // Product Performance Report (JSON response)
    public function productPerformance(Request $request)
{
    try {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'sort_by' => 'nullable|in:quantity,revenue',
        ]);

        $products = Product::with('category') // Eager load the category
            ->selectRaw('
                products.*,
                SUM(sale_items.quantity) as total_sold,
                SUM(sale_items.quantity * sale_items.price) as total_revenue
            ')
            ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($request->has('category_id'), function ($q) use ($validated) {
                if (is_null($validated['category_id'])) {
                    $q->whereNull('products.category_id');
                } else {
                    $q->where('products.category_id', $validated['category_id']);
                }
            })
            ->when($request->sort_by, function ($q) use ($validated) {
                $sortField = $validated['sort_by'] === 'quantity' ? 'total_sold' : 'total_revenue';
                $q->orderBy($sortField, 'desc');
            })
            ->groupBy('products.id')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching product performance data: ' . $e->getMessage(), [
            'exception' => $e,
            'request_data' => $request->all(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while generating the product performance report.',
        ], 500);
    }
}

    // Category-wise Sales Report (JSON response)
    public function categorySales(Request $request)
    {
        $categories = Category::selectRaw('
                categories.*,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.quantity * sale_items.price) as total_revenue
            ')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('categories.id')
            ->orderBy('total_revenue', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    // Inventory Status Report (JSON response)
    public function inventoryReport()
    {
        // Order by a column that actually exists. The old code ordered by
        // "remaining_units", which is commented out of the products migration:
        // SQLite silently ignored it and MySQL/Postgres would 500.
        $products = Product::with(['category', 'supplier'])
            ->orderBy('stock')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    // Expiry Report (JSON response)
    public function expiryReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'threshold' => 'nullable|integer|min:1|max:365',
            ]);

            $threshold = (int) ($validated['threshold'] ?? 30);
            $expiryDate = Carbon::now()->addDays($threshold);

            $products = Product::whereNotNull('exp_date')
                ->where('exp_date', '<=', $expiryDate)
                ->orderBy('exp_date')
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'threshold_days' => $threshold,
                    'products' => $products,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating expiry report: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while generating the expiry report.',
            ], 500);
        }
    }

    // Stock Movement Report (JSON response)
    public function stockMovement(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $products = Product::with(['category'])
                ->withSum([
                    'productions as total_produced' => function ($query) use ($validated) {
                        $this->applyDateFilters($query, $validated);
                    }
                ], 'quantity')
                ->withSum([
                    'saleItems as total_sold' => function ($query) use ($validated) {
                        $this->applyDateFilters($query->whereHas('sale'), $validated);
                    }
                ], 'quantity')
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching stock movement report: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(), // Log request data for debugging
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while generating the stock movement report.',
            ], 500);
        }
    }

    // Helper method for date filtering
    private function applyDateFilters($query, $filters)
    {
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
    }
}