<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionController extends Controller
{
    /**
     * Display a listing of production batches.
     */
    public function index()
    {
        $batches = Production::with('product')->latest()->get();
        return inertia('ProductionBatches/Index', compact('batches'));
    }

    /**
     * Show the form for creating a new batch.
     */
    public function create()
    {
        $categories = Product::all();
        return inertia('ProductionBatches/Create', compact('categories'));
    }

    /**
     * Store a newly created batch in the database.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'material_cost' => 'required|numeric',
            'labor_cost' => 'required|numeric',
            'overhead_cost' => 'required|numeric',
            'batch_status' => 'required|in:Pending,In Progress,Completed,Cancelled',
        ]);


        $batchId = Production::max('id') + 1;

        // Update batch_number with the formatted string
        $validated['batch_number'] = 'BATCH-' . str_pad($batchId, 5, '0', STR_PAD_LEFT);
        Production::create($validated);

        // Update product stock if the batch status is "Completed"
        if ($validated['batch_status'] === 'Completed') {
            $product = Product::findOrFail($validated['product_id']);
            $product->stock += $validated['quantity'];
            $product->save(); 
        }


        // Create the production batch

        return redirect()->route('productions.index')->with('success', 'Batch created successfully.');
    }

    /**
     * Show the form for editing a batch.
     */
    public function edit(Production $production)
    {

        $product = Product::all();
    

        return Inertia::render('ProductionBatches/Edit', [
            'production' => $production,
            'product' => $product,
        ]);
    }

    /**
     * Update a batch in the database.
     */
    public function update(Request $request, Production $production)
    {
        // Validate the request data

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'material_cost' => 'required|string',
            'labor_cost' => 'required|string',
            'overhead_cost' => 'required|string',
            'batch_status' => 'required|in:Pending,In Progress,Completed,Cancelled',
        ]);


        if ($validated['batch_status'] === 'Completed') {
            $product = Product::findOrFail($validated['product_id']);
            $product->stock += $validated['quantity'];
            $product->save(); // Save the updated stock to the database
        }

        // Update the ProductionBatch
        $production->update($validated);

        // Redirect with success message
        return redirect()->route('productions.index')->with('success', 'Batch updated successfully.');
    }


    /**
     * Remove a batch from the database.
     */
    public function destroy(Production $production)
    {
        $production->delete();
        return redirect()->route('productions.index')->with('success', 'Batch deleted successfully.');
    }
}
