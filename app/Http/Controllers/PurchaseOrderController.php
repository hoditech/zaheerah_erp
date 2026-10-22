<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    // **PURCHASE ORDERS CRUD**

    // List all purchase orders
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'category'])->get();

        return Inertia('procurement/PurchaseOrder/Index', compact('purchaseOrders'));
    }

    // Show form to create a new purchase order
    public function create()
    {
        $user = auth()->user();

        $suppliers = Supplier::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('category_id', $user->category_id);
        })->get();

        $categories = Category::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('id', $user->category_id);
        })->get();

        return Inertia('procurement/PurchaseOrder/Create', compact('suppliers', 'categories'));
    }

    // Store a new purchase order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:purchase_orders,order_number|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'total_cost' => 'required|numeric|min:0',
            'order_date' => 'required|date',
            'delivery_status' => 'required|in:Pending,Delivered,Cancelled',
        ]);

        $purchaseOrder = PurchaseOrder::create($validated);

        // Log activity
        log_activity('Purchase Order Created', "Order #{$purchaseOrder->order_number} was created.");

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created successfully.');
    }

    // Edit purchase order
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();

        $suppliers = Supplier::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('category_id', $user->category_id);
        })->get();

        $categories = Category::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('id', $user->category_id);
        })->get();

        return Inertia('procurement/PurchaseOrder/Edit', compact('purchaseOrder', 'suppliers', 'categories'));
    }

    // Update purchase order
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255|unique:purchase_orders,order_number,' . $purchaseOrder->id,
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'total_cost' => 'required|numeric|min:0',
            'order_date' => 'required|date',
            'delivery_status' => 'required|in:Pending,Delivered,Cancelled',
        ]);

        $purchaseOrder->update($validated);

        // Log activity
        log_activity('Purchase Order Updated', "Order #{$purchaseOrder->order_number} was updated.");

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated successfully.');
    }

    // Delete purchase order
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $orderNumber = $purchaseOrder->order_number;
        $purchaseOrder->delete();

        // Log activity
        log_activity('Purchase Order Deleted', "Order #{$orderNumber} was deleted.");

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}
