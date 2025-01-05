<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Production;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        //    $categories = Category::with(['products.productions'])->get();
        //    dd($categories->toArray());
        
        
        

        // Retrieve total revenue (total sales)
        $totalRevenue = Sale::sum('total_amount');
        // Retrieve total cost (sum of material cost, labor cost, and overhead cost for all productions)
        $totalCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));

        //dd($totalCost);
        // Calculate total profit (Revenue - Cost)
        $totalProfit = $totalRevenue - $totalCost;

        // Retrieve stock levels
        $totalStock = Product::sum('stock');

        // Get categories and their financial metrics (Revenue, Cost, and Profit)
        $categories = Category::with(['products.saleItems', 'products.productions'])
        ->get()
        ->map(function ($category) {
            // Calculate total revenue from sales
            $categoryRevenue = $category->products->sum(function ($product) {
                return $product->saleItems->sum(function ($saleItem) {
                    return $saleItem->price * $saleItem->quantity;
                });
            });
    
            // Estimate production cost (if required)
            $categoryCost = $category->products->sum(function ($product) {
                return $product->productions->sum(function ($production) {
                    return ($production->material_cost + $production->labor_cost + $production->overhead_cost) ?: 0;
                });
            });


            $totalStock = $category->products->sum('stock');

            
    
            // Calculate profit
            $categoryProfit = $categoryRevenue - $categoryCost;
    
            // Return mapped category data
            return [
                'id' => $category->id,
                'name' => $category->name,
                'revenue' => '₦' . number_format($categoryRevenue, 2),
                'cost' => '₦' . number_format($categoryCost, 2),
                'profit' => '₦' . number_format($categoryProfit, 2),
                'inventory' => number_format($totalStock, 0). ' items',
            ];
        });




        $users = Employee::all();


        // Recent Activities (demo data, replace with actual)
        $recentActivities = [
            ['description' => 'Added new record in Farm Produce', 'time' => '5 min ago'],
            ['description' => 'Updated Water Production records', 'time' => '15 min ago'],
        ];

        

        // Users (demo data, replace with actual)


        return inertia('Dashboard', [
            'totalRevenue' => '₦' . number_format($totalRevenue, 2),
            'totalProfit' => '₦' . number_format($totalProfit, 2),
            'totalCost' => '₦' . number_format($totalCost, 2),
            'totalStock' => number_format($totalStock),
            'categories' => $categories,
            'recentActivities' => $recentActivities,
            'users' => $users,
        ]);
    }
}
