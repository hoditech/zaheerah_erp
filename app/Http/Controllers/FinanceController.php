<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Employee;
use App\Models\InventoryTransaction;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\User;
use DB;

class FinanceController extends Controller
{
    public function index()
    {
        try {
            // Total Revenue (Sales)
            $totalRevenue = Sale::sum('total_amount');
    
            // Total Expenses (Production + Inventory Purchases)
            $totalProductionCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));
            $totalInventoryCost = InventoryTransaction::whereIn('type', ['restock', 'production', 'initial'])
                                ->sum(DB::raw('quantity * unit_cost'));
    
            // Payroll Expenses
            $totalPayrollExpense = Payroll::sum('net_salary');
    
            // Profit Calculation
            $totalExpenses = $totalProductionCost + $totalInventoryCost;
            $totalProfit = $totalRevenue - ($totalExpenses + $totalPayrollExpense);
    
            // Inventory Valuation
            $totalStockValue = Product::sum(DB::raw('stock * unit_cost'));
            $totalStockCount = Product::sum('stock');
    
            // Category Breakdown (Optimized)
            $categories = Category::with(['products.saleItems', 'products.inventoryTransactions', 'products.productions'])
                ->where('id', '!=', 1)
                ->get()
                ->map(function ($category) {
                    // Category Revenue
                    $categoryRevenue = $category->products->sum(function ($product) {
                        return $product->saleItems->sum(fn($sale) => $sale->price * $sale->quantity);
                    });
    
                    // Category Costs (Production + Inventory)
                    $categoryCost = $category->products->sum(function ($product) {
                        $productionCost = $product->productions->sum(fn($p) => $p->material_cost + $p->labor_cost + $p->overhead_cost);
                        $inventoryCost = $product->inventoryTransactions->sum(fn($t) => $t->quantity * $t->unit_cost);
                        return $productionCost + $inventoryCost;
                    });
    
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'revenue' =>'₦' . number_format( $categoryRevenue, 2),
                        'cost' => '₦' . number_format($categoryCost, 2),
                        'profit' =>'₦' . number_format( $categoryRevenue - $categoryCost, 2),
                        'stockValue' =>'₦' . number_format($category->products->sum(fn($p) => $p->stock * $p->unit_cost), 2),
                        'totalStock' => number_format($category->products->sum('stock'), 0),
                    ];
                });

               // dd($categories);
    
            // Monthly Trends (Example for 3 months)
            // $monthlyData = Sale::selectRaw('
            //         DATE_FORMAT(created_at, "%Y-%m") as month,
            //         SUM(total_amount) as revenue
            //     ')
            //     ->groupBy('month')
            //     ->orderBy('month', 'desc')
            //     ->limit(3)
            //     ->get();
    
            // Debug output if needed
            // dd($categories);
    
            return inertia('finance/show', [
                // Main Metrics
                'totalRevenue' => '₦' . number_format($totalRevenue, 2),
                'totalExpenses' => '₦' . number_format($totalExpenses, 2),
                'totalCost' => '₦' . number_format($totalExpenses, 2),
                'totalPayrollExpense' => $totalPayrollExpense,
                'totalProfit' =>'₦' . number_format($totalProfit, 2),
    
                // Inventory Metrics
                'totalStockValue' =>'₦' . number_format($totalStockValue, 2),
                'totalStockCount' => $totalStockCount,
    
                // Breakdowns
                'categories' => $categories,
               /// 'monthlyTrends' => $monthlyData,
    
                // Formatting (optional - can do in frontend)
                
            ]);
    
        } catch (\Exception $e) {
            dd('Error occurred:', $e->getMessage(), $e->getTraceAsString());
        }
    }
    
}