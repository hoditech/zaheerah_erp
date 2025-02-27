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
        // Get the current user's category_id
        $categoryId = auth()->user()->category_id;

        // Fetch production batches based on the user's category_id
        $batches = Production::with('product')
            ->when($categoryId != 1, function ($query) use ($categoryId) {
                // Filter production batches by products that belong to the user's category
                $query->whereHas('product', function ($productQuery) use ($categoryId) {
                    $productQuery->where('category_id', $categoryId);
                });
            })
            ->latest()
            ->get();

        return inertia('ProductionBatches/Index', compact('batches'));
    }

    /**
     * Show the form for creating a new batch.
     */
    public function create()
    {
        $categoryId = auth()->user()->category_id;

        $products = Product::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('category_id', $categoryId);
        })
            ->get();
        return inertia('ProductionBatches/Create', compact('products'));
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
        $production = Production::create($validated);

        // Update product stock if the batch status is "Completed"
        if ($validated['batch_status'] === 'Completed') {
            $product = Product::findOrFail($validated['product_id']);
            $product->stock += $validated['quantity'];
            $product->save();

            // Log activity
        }

        log_activity('Production Created', 'Created production batch: ' . $production->batch_number);

        // Create the production batch

        return redirect()->route('productions.index')->with('success', 'Batch created successfully.');
    }

    /**
     * Show the form for editing a batch.
     */
    public function edit(Production $production)
    {

        $categoryId = auth()->user()->category_id;

        $products = Product::when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('category_id', $categoryId);
        })
            ->get();
        return Inertia::render('ProductionBatches/Edit', [
            'production' => $production,
            'products' => $products,
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

        log_activity('Production Updated', 'Updated production batch: ' . $production->batch_number);


        // Redirect with success message
        return redirect()->route('productions.index')->with('success', 'Batch updated successfully.');
    }


    /**
     * Remove a batch from the database.
     */
    public function destroy(Production $production)
    {
        $batchNumber = $production->batch_number;
        $production->delete();

        // Log activity
        log_activity('Production Deleted', 'Deleted production batch: ' . $batchNumber);
        return redirect()->route('productions.index')->with('success', 'Batch deleted successfully.');
    }
}
