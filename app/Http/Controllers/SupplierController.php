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
        $supplies = Supplier::with('category')->get();
        //dd($supplies);
        return Inertia('procurement/Suppliers/Index', compact('supplies'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:15',
            'address' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:Active,Inactive',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        dd($e->errors()); // Output validation errors
    }

        Supplier::create($request->all());
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    // Edit supplier
    public function edit($id)
    {
        $categories = Category::all();
        $supplier = Supplier::findOrFail($id);
       // dd($supplier);

        return Inertia('procurement/Suppliers/Edit', compact( 'supplier','categories'));
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:15',
            'address' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        //dd($request);

        $supplier->update($request->all());
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
