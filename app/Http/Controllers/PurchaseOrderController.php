<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

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
        $categoryId = auth()->user()->category_id;
        $suppliers = Supplier::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('category_id', $categoryId);
        })->get();

        $categories = Category::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('id', $categoryId);
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

        // dd($validated);

        PurchaseOrder::create($validated);
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created successfully.');
    }

    // Edit purchase order
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $categoryId = auth()->user()->category_id;
        $suppliers = Supplier::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('category_id', $categoryId);
        })->get();

        $categories = Category::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('id', $categoryId);
        })->get();
        return Inertia('procurement/PurchaseOrder/Edit', compact('purchaseOrder', 'suppliers', 'categories'));
    }

    // Update purchase order
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'order_number' => 'required|string|max:255|unique:purchase_orders,order_number,' . $purchaseOrder->id,
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'total_cost' => 'required|numeric|min:0',
            'order_date' => 'required|date',
            'delivery_status' => 'required|in:Pending,Delivered,Cancelled',
        ]);

        $purchaseOrder->update($request->all());
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated successfully.');
    }

    // Delete purchase order
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}
