<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        // Total Revenue (Sales)
        $totalRevenue = (float) Sale::sum('total_amount');

        // Cost of goods. Production batches are the single source of production
        // cost, and only "restock" inventory movements are purchases. The old
        // code also added the "production" and "initial" movements, which
        // double-counted every production batch and treated opening stock as an
        // expense in the period.
        $totalProductionCost = (float) Production::sum(
            DB::raw('material_cost + labor_cost + overhead_cost')
        );
        $totalPurchaseCost = (float) InventoryTransaction::where('type', 'restock')
            ->sum(DB::raw('quantity * unit_cost'));

        // Payroll expense
        $totalPayrollExpense = (float) Payroll::sum('net_salary');

        // Profit Calculation
        $totalExpenses = $totalProductionCost + $totalPurchaseCost;
        $totalProfit = $totalRevenue - ($totalExpenses + $totalPayrollExpense);

        // Inventory Valuation
        $totalStockValue = (float) Product::sum(DB::raw('stock * COALESCE(unit_cost, 0)'));
        $totalStockCount = (int) Product::sum('stock');

        // Category breakdown, aggregated in SQL instead of loading every sale
        // line and inventory row into PHP on each page view.
        $categories = Category::query()
            ->where('name', '!=', 'General')
            ->get()
            ->map(function (Category $category) {
                $categoryRevenue = (float) SaleItem::query()
                    ->join('products', 'products.id', '=', 'sale_items.product_id')
                    ->where('products.category_id', $category->id)
                    ->sum(DB::raw('sale_items.price * sale_items.quantity'));

                $productionCost = (float) Production::query()
                    ->join('products', 'products.id', '=', 'productions.product_id')
                    ->where('products.category_id', $category->id)
                    ->sum(DB::raw('productions.material_cost + productions.labor_cost + productions.overhead_cost'));

                $purchaseCost = (float) InventoryTransaction::query()
                    ->join('products', 'products.id', '=', 'inventory_transactions.product_id')
                    ->where('products.category_id', $category->id)
                    ->where('inventory_transactions.type', 'restock')
                    ->sum(DB::raw('inventory_transactions.quantity * inventory_transactions.unit_cost'));

                $categoryCost = $productionCost + $purchaseCost;

                $stockValue = (float) Product::where('category_id', $category->id)
                    ->sum(DB::raw('stock * COALESCE(unit_cost, 0)'));
                $stockCount = (int) Product::where('category_id', $category->id)->sum('stock');

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'revenue' => '₦' . number_format($categoryRevenue, 2),
                    'cost' => '₦' . number_format($categoryCost, 2),
                    'profit' => '₦' . number_format($categoryRevenue - $categoryCost, 2),
                    'stockValue' => '₦' . number_format($stockValue, 2),
                    'totalStock' => number_format($stockCount, 0),
                ];
            });

        return inertia('finance/show', [
            // Main Metrics
            'totalRevenue' => '₦' . number_format($totalRevenue, 2),
            'totalExpenses' => '₦' . number_format($totalExpenses, 2),
            'totalCost' => '₦' . number_format($totalExpenses, 2),
            'totalPayrollExpense' => $totalPayrollExpense,
            'totalProfit' => '₦' . number_format($totalProfit, 2),

            // Inventory Metrics
            'totalStockValue' => '₦' . number_format($totalStockValue, 2),
            'totalStockCount' => $totalStockCount,

            // Breakdowns
            'categories' => $categories,
        ]);
    }
}
