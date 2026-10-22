<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Revenue
        $totalRevenue = (float) Sale::sum('total_amount');

        // Same cost definition the Finance page uses, so the two screens stop
        // disagreeing (production/initial movements are not counted twice) and
        // payroll is included.
        $totalProductionCost = (float) Production::sum(
            DB::raw('material_cost + labor_cost + overhead_cost')
        );
        $totalPurchaseCost = (float) InventoryTransaction::where('type', 'restock')
            ->sum(DB::raw('quantity * unit_cost'));
        $totalPayrollExpense = (float) Payroll::sum('net_salary');

        $totalCost = $totalProductionCost + $totalPurchaseCost + $totalPayrollExpense;

        // Profit Calculation
        $totalProfit = $totalRevenue - $totalCost;

        // User Metrics (unchanged)
        $usersCount = User::count();
        $activeUsersCount = User::where('can_login', true)->count();
        $newUsersCount = User::whereDate('created_at', now()->toDateString())->count();

        // Activity Logs (unchanged)
        $user = auth()->user();
        $logsQuery = $user->hasRole('Super Admin')
            ? ActivityLog::latest()->with('user')->take(300)
            : $user->activityLogs()->latest()->with('user')->take(200);

        $logs = $logsQuery->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at,
                'user_name' => $log->user?->name ?? 'Unknown',
            ];
        });

        // Categories Breakdown, aggregated in SQL (see FinanceController).
        $categories = Category::query()
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

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'revenue' => '₦' . number_format($categoryRevenue, 2),
                    'cost' => '₦' . number_format($categoryCost, 2),
                    'profit' => '₦' . number_format($categoryRevenue - $categoryCost, 2),
                    'inventory' => number_format((int) Product::where('category_id', $category->id)->sum('stock'), 0) . ' items',
                ];
            });

        return inertia('Dashboard', [
            // User metrics
            'usersCount' => $usersCount,
            'activeUsersCount' => $activeUsersCount,
            'newUsersCount' => $newUsersCount,

            // Financial metrics
            'totalRevenue' => '₦' . number_format($totalRevenue, 2),
            'totalCost' => '₦' . number_format($totalCost, 2),
            'totalProfit' => '₦' . number_format($totalProfit, 2),

            // Activity logs
            'logs' => $logs,

            // Categories breakdown
            'categories' => $categories,
        ]);
    }
}
