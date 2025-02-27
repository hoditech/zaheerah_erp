<?php

namespace App\Http\Controllers;


use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\User;
use DB;


class DashboardController extends Controller
{
    public function index()
    {

        // // Retrieve total revenue (total sales)
        $totalRevenue = Sale::sum('total_amount');
        // // Retrieve total cost (sum of material cost, labor cost, and overhead cost for all productions)
        $totalCost = Production::sum(DB::raw('material_cost + labor_cost + overhead_cost'));

        // //dd($totalCost);
// // Calculate total profit (Revenue - Cost)
        $totalProfit = $totalRevenue - $totalCost;

        // // Retrieve stock levels
        $totalStock = Product::sum('stock');


        $usersCount = User::count();
        $activeUsersCount = User::where('can_login', true)->count(); // Adjust based on your status column
        $newUsersCount = User::whereDate('created_at', now()->toDateString())->count(); // Users registered today

        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            // Admin: Fetch the latest 100 logs across all users
            $logsQuery = ActivityLog::latest()->with('user')->take(300);

        } else {
            // Non-admin: Fetch only the user's own logs
            $logsQuery = $user->activityLogs()->latest()->with('user')->take(200);
        }

        $logs = $logsQuery->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at,
                'user_name' => $log->user ? $log->user->name : 'Unknown',
            ];
        });

        //categories
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
                    'inventory' => number_format($totalStock, 0) . ' items',
                ];
            });

        return inertia('Dashboard', [
            'usersCount' => $usersCount,
            'activeUsersCount' => $activeUsersCount,
            'newUsersCount' => $newUsersCount,
            'logs' => $logs,
            'totalRevenue' => '₦' . number_format($totalRevenue, 2),
            'totalProfit' => '₦' . number_format($totalProfit, 2),
            'totalCost' => '₦' . number_format($totalCost, 2),
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
