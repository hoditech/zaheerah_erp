<?php

namespace App\Http\Controllers;


use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\User;
use DB;


class DashboardController extends Controller
{
    public function index()
    {
        // Total Revenue
        $totalRevenue = Sale::sum('total_amount');
    
        // Total Costs (Production + Inventory Purchases)
        $totalProductionCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));
        $totalInventoryCost = InventoryTransaction::whereIn('type', ['restock', 'production', 'initial'])
                            ->sum(DB::raw('quantity * unit_cost'));
        $totalCost = $totalProductionCost + $totalInventoryCost;
    
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
    
        // Categories Breakdown (with accurate costs)
        $categories = Category::with(['products.saleItems', 'products.productions', 'products.inventoryTransactions'])
            ->get()
            ->map(function ($category) {
                $categoryRevenue = $category->products->sum(function ($product) {
                    return $product->saleItems->sum(fn($si) => $si->price * $si->quantity);
                });
    
                $categoryCost = $category->products->sum(function ($product) {
                    $productionCost = $product->productions->sum(fn($p) => $p->material_cost + $p->labor_cost + $p->overhead_cost);
                    $inventoryCost = $product->inventoryTransactions->sum(fn($t) => $t->quantity * $t->unit_cost);
                    return $productionCost + $inventoryCost;
                });
    
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'revenue' => '₦' . number_format($categoryRevenue, 2),
                    'cost' => '₦' . number_format($categoryCost, 2),
                    'profit' => '₦' . number_format($categoryRevenue - $categoryCost, 2),
                    'inventory' => number_format($category->products->sum('stock'), 0) . ' items',
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


// //    $categories = Category::with(['products.productions'])->get();
// //    dd($categories->toArray());




// // Retrieve total revenue (total sales)
// $totalRevenue = Sale::sum('total_amount');
// // Retrieve total cost (sum of material cost, labor cost, and overhead cost for all productions)
// $totalCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));

// //dd($totalCost);
// // Calculate total profit (Revenue - Cost)
// $totalProfit = $totalRevenue - $totalCost;

// // Retrieve stock levels
// $totalStock = Product::sum('stock');

// // Get categories and their financial metrics (Revenue, Cost, and Profit)
// $categories = Category::with(['products.saleItems', 'products.productions'])
// ->where('id', '!=', 1)
// ->get()
// ->map(function ($category) {
//     // Calculate total revenue from sales
//     $categoryRevenue = $category->products->sum(function ($product) {
//         return $product->saleItems->sum(function ($saleItem) {
//             return $saleItem->price * $saleItem->quantity;
//         });
//     });

//     // Estimate production cost (if required)
//     $categoryCost = $category->products->sum(function ($product) {
//         return $product->productions->sum(function ($production) {
//             return ($production->material_cost + $production->labor_cost + $production->overhead_cost) ?: 0;
//         });
//     });


//     $totalStock = $category->products->sum('stock');



//     // Calculate profit
//     $categoryProfit = $categoryRevenue - $categoryCost;

//     // Return mapped category data
//     return [
//         'id' => $category->id,
//         'name' => $category->name,
//         'revenue' => '₦' . number_format($categoryRevenue, 2),
//         'cost' => '₦' . number_format($categoryCost, 2),
//         'profit' => '₦' . number_format($categoryProfit, 2),
//         'inventory' => number_format($totalStock, 0). ' items',
//     ];
// });




// $users = Employee::all();


// // Recent Activities (demo data, replace with actual)
// $recentActivities = [
//     ['description' => 'Added new record in Farm Produce', 'time' => '5 min ago'],
//     ['description' => 'Updated Water Production records', 'time' => '15 min ago'],
// ];



// // Users (demo data, replace with actual)


//     return inertia('Dashboard', [
//         ''
//         // 'totalRevenue' => '₦' . number_format($totalRevenue, 2),
//         // 'totalProfit' => '₦' . number_format($totalProfit, 2),
//         // 'totalCost' => '₦' . number_format($totalCost, 2),
//         // 'totalStock' => number_format($totalStock),
//         // 'categories' => $categories,
//         // 'recentActivities' => $recentActivities,
//         // 'users' => $users,
//     ]);
// }
//}
