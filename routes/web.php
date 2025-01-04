<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\productController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', fn() => Inertia::render('POS'))->name('pos');
    //Route::get('/products', fn() => Inertia::render('ProductPage'))->name('products');
    Route::get('/users', fn() => Inertia::render('Users'))->name('users');
    Route::get('/stores', fn() => Inertia::render('Stores'))->name('stores');
    Route::get('/settings', fn() => Inertia::render('Settings'))->name('settings');
    
    Route::get('/products', [productController::class, 'index'])->name('products');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');


    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/make-sale', [PosController::class, 'makeSale']);
    Route::post('/pos/generate-receipt', [PosController::class, 'generateReceipt']);


    Route::resource('employees', EmployeeController::class);
    Route::resource('productions', ProductionController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
});

require __DIR__ . '/auth.php';
