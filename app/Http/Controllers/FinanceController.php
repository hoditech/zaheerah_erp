<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Employee;
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
        // Retrieve total revenue (total sales)
        $totalRevenue = Sale::sum('total_amount');

        // Retrieve total production cost (sum of material cost, labor cost, and overhead cost for all productions)
        $totalCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));

        // Retrieve total payroll cost (salaries + commissions + bonuses - deductions - tax)
        $totalPayrollExpense = Payroll::sum(DB::raw('net_salary'));


        // Calculate total profit (Revenue - Production Cost - Payroll Expense)
        $totalProfit = $totalRevenue - ($totalCost + $totalPayrollExpense);

        // dd([
        //     'totalRevenue'=> $totalRevenue,
        //     'totalCost'=> $totalCost,
        //     'totalPayrollExpense'=> $totalPayrollExpense,
        // ]);

        // Get total stock levels
        $totalStock = Product::sum('stock');

        // Get categories and their financial metrics (Revenue, Cost, Profit)
        $categories = Category::with(['products.saleItems', 'products.productions'])
            ->where('id', '!=', 1)
            ->get()
            ->map(function ($category) {
                $categoryRevenue = $category->products->sum(function ($product) {
                    return $product->saleItems->sum(fn($saleItem) => $saleItem->price * $saleItem->quantity);
                });

                $categoryCost = $category->products->sum(function ($product) {
                    return $product->productions->sum(fn($production) => ($production->material_cost + $production->labor_cost + $production->overhead_cost) ?: 0);
                });

                $totalStock = $category->products->sum('stock');

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'revenue' => '₦' . number_format($categoryRevenue, 2),
                    'cost' => '₦' . number_format($categoryCost, 2),
                    'profit' => '₦' . number_format($categoryRevenue - $categoryCost, 2),
                    'inventory' => number_format($totalStock, 0) . ' items',
                ];
            });


        // // Retrieve monthly revenue
        // $monthlyRevenue = Sale::selectRaw("strftime('%m', created_at) as month, SUM(total_amount) as total")
        //     ->groupBy('month')
        //     ->orderBy('month')
        //     ->pluck('total', 'month');


        // // Retrieve monthly expenses (sum of production costs)
        // $monthlyExpenses = Production::selectRaw("strftime('%m', created_at) as month, SUM(material_cost + labor_cost + overhead_cost) as total")
        //     ->groupBy('month')
        //     ->orderBy('month')
        //     ->pluck('total', 'month');

        // // Prepare labels (months)
        // $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // // Prepare chart data for frontend
        // $chartData = [
        //     'labels' => $months,
        //     'datasets' => [
        //         [
        //             'label' => 'Revenue',
        //             'data' => array_values(array_replace(array_fill(0, 12, 0), $monthlyRevenue->toArray())),
        //             'backgroundColor' => '#4CAF50',
        //         ],
        //         [
        //             'label' => 'Expenses',
        //             'data' => array_values(array_replace(array_fill(0, 12, 0), $monthlyExpenses->toArray())),
        //             'backgroundColor' => '#FF5252',
        //         ],
        //     ],
        // ];

        return inertia('finance/show', [
            'totalRevenue' => '₦' . number_format($totalRevenue, 2),
            'totalCost' => '₦' . number_format($totalCost, 2),
            'totalPayrollExpense' => '₦' . number_format($totalPayrollExpense, 2),
            'totalProfit' => '₦' . number_format($totalProfit, 2),
            'totalStock' => number_format($totalStock),
            'categories' => $categories,
        ]);
    }
}