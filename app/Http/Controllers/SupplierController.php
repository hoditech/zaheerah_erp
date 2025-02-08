<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    // **SUPPLIERS CRUD**

    // List all suppliers
    public function index()
    {
        $suppliers = Supplier::with('category')->get();
        return Inertia('procurement/Suppliers/Index', compact('suppliers'));
    }

    // Show form to create a new supplier
    public function create()
    {
        $categories = Category::all();
        return Inertia('procurement/Suppliers/Create', compact('categories'));
    }

    // Store a new supplier
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_email' => 'required|email|unique:suppliers,contact_email',
                'contact_phone' => 'required|string|max:15|unique:suppliers,contact_phone',
                'address' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:Active,Inactive',
            ]);

            $supplier = Supplier::create($validated);

            // Log activity
            log_activity('Supplier Created', "Supplier {$supplier->name} was added.");

            return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    // Edit supplier
    public function edit(Supplier $supplier)
    {
        $categories = Category::all();
        return Inertia('procurement/Suppliers/Edit', compact('supplier', 'categories'));
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_email' => 'required|email|unique:suppliers,contact_email,' . $supplier->id,
                'contact_phone' => 'required|string|max:15|unique:suppliers,contact_phone,' . $supplier->id,
                'address' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:Active,Inactive',
            ]);

            $supplier->update($validated);

            // Log activity
            log_activity('Supplier Updated', "Supplier {$supplier->name} was updated.");

            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        $supplierName = $supplier->name;
        $supplier->delete();

        // Log activity
        log_activity('Supplier Deleted', "Supplier {$supplierName} was deleted.");

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}