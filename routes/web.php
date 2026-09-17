<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\OnlineLeadController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\productController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WelcomeController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', [WelcomeController::class, 'index']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products')
        ->middleware('permission:view_products');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create')
        ->middleware('permission:create_products');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store')
        ->middleware('permission:create_products');

    Route::get('/products/{product}', [productController::class, 'edit'])
    ->name('products.edit')
    ->middleware('permission:edit_products');

    Route::put('/products/{product}', [productController::class, 'update'])
    ->name('products.update')
    ->middleware('permission:edit_products');

    Route::delete('/products/{product}', [productController::class, 'destroy'])
    ->name('products.destroy')
    ->middleware('permission:delete_products');

    Route::get('/pos', [PosController::class, 'index'])
        ->name('pos')
        ->middleware('permission:view_sales');

    Route::post('/pos/make-sale', [PosController::class, 'makeSale'])
        ->middleware('permission:create_sales');

    Route::post('/pos/generate-receipt', [PosController::class, 'generateReceipt'])
        ->middleware('permission:generate_sales_reports');
    Route::get('/pos/receipt/{receiptNumber}', [PosController::class, 'getReceipt'])->name('pos.receipt.show'); 


    Route::get('employees', [EmployeeController::class, 'index'])
        ->name('employees.index')
        ->middleware('permission:view_users');

    Route::get('employees/create', [EmployeeController::class, 'create'])
        ->name('employees.create')
        ->middleware('permission:manage_users');

    Route::post('employees', [EmployeeController::class, 'store'])
        ->name('employees.store')
        ->middleware('permission:manage_users');

    Route::get('employees/{employee}', [EmployeeController::class, 'show'])
        ->name('employees.show')
        ->middleware('permission:view_users');

    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])
        ->name('employees.edit')
        ->middleware('permission:manage_users');

    Route::put('employees/{employee}', [EmployeeController::class, 'update'])
        ->name('employees.update')
        ->middleware('permission:manage_users');

    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])
        ->name('employees.destroy')
        ->middleware('permission:manage_users');

    Route::get('suppliers', [SupplierController::class, 'index'])
        ->name('suppliers.index')
        ->middleware('permission:manage_suppliers');

    Route::get('suppliers/create', [SupplierController::class, 'create'])
        ->name('suppliers.create')
        ->middleware('permission:manage_suppliers');

    Route::post('suppliers', [SupplierController::class, 'store'])
        ->name('suppliers.store')
        ->middleware('permission:manage_suppliers');

    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])
        ->name('suppliers.show')
        ->middleware('permission:manage_suppliers');

    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
        ->name('suppliers.edit')
        ->middleware('permission:manage_suppliers');

    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])
        ->name('suppliers.update')
        ->middleware('permission:manage_suppliers');

    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->name('suppliers.destroy')
        ->middleware('permission:manage_suppliers');

    Route::get('productions', [ProductionController::class, 'index'])
        ->name('productions.index')
        ->middleware('permission:view_productions');

    Route::get('productions/create', [ProductionController::class, 'create'])
        ->name('productions.create')
        ->middleware('permission:create_productions');

    Route::post('productions', [ProductionController::class, 'store'])
        ->name('productions.store')
        ->middleware('permission:create_productions');

    Route::get('productions/{production}', [ProductionController::class, 'show'])
        ->name('productions.show')
        ->middleware('permission:view_productions');

    Route::get('productions/{production}/edit', [ProductionController::class, 'edit'])
        ->name('productions.edit')
        ->middleware('permission:update_productions');

    Route::put('productions/{production}', [ProductionController::class, 'update'])
        ->name('productions.update')
        ->middleware('permission:update_productions');

    Route::delete('productions/{production}', [ProductionController::class, 'destroy'])
        ->name('productions.destroy')
        ->middleware('permission:delete_productions');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])
        ->name('purchase-orders.index')
        ->middleware('permission:view_purchase_orders');

    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])
        ->name('purchase-orders.create')
        ->middleware('permission:create_purchase_orders');

    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])
        ->name('purchase-orders.store')
        ->middleware('permission:create_purchase_orders');

    Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])
        ->name('purchase-orders.show')
        ->middleware('permission:view_purchase_orders');

    Route::get('purchase-orders/{purchase_order}/edit', [PurchaseOrderController::class, 'edit'])
        ->name('purchase-orders.edit')
        ->middleware('permission:update_purchase_orders');

    Route::put('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update'])
        ->name('purchase-orders.update')
        ->middleware('permission:update_purchase_orders');

    Route::delete('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])
        ->name('purchase-orders.destroy')
        ->middleware('permission:delete_purchase_orders');

    // Routes accessible to everyone
    Route::get('notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::get('notices/{notice}', [NoticeController::class, 'show'])->name('notices.show');

    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');

    // Enquiries that arrived from the public website (quote requests, contact form).
    Route::get('online-leads', [OnlineLeadController::class, 'index'])->name('online-leads.index');
    Route::patch('online-leads/{lead}', [OnlineLeadController::class, 'updateStatus'])->name('online-leads.update');

    Route::get('/products/{product}/stock', [PosController::class, 'getStock']);




});

// Routes restricted to Super Admin
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');

    Route::get('/pos/sale-history', [PosController::class, 'saleHistory'])->name('sale-history');

    Route::resource('payrolls', PayrollController::class);
    Route::post('payrolls/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('payrolls.markPaid');

    Route::get('/users', function () {
        $users = User::select('id', 'name')->get(); // Fetch only id & name

        return response()->json([
            'users' => $users,
        ]);
    });

    // Restricted routes for creating, updating, and deleting notices and events
    Route::post('notices', [NoticeController::class, 'store'])->name('notices.store');
    Route::put('notices/{notice}', [NoticeController::class, 'update'])->name('notices.update');
    Route::delete('notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');


});




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});

Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
        Route::get('/products', [ReportController::class, 'productPerformance'])->name('reports.products');
        Route::get('/categories', [ReportController::class, 'categorySales'])->name('reports.categories');
        Route::get('/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
        Route::get('/expiry', [ReportController::class, 'expiryReport'])->name('reports.expiry');
        Route::get('/stock-movement', [ReportController::class, 'stockMovement'])->name('reports.stock-movement');
    });
});

require __DIR__ . '/auth.php';
