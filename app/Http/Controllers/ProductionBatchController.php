<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\productionBatch;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionBatchController extends Controller
{
    /**
     * Display a listing of production batches.
     */
    public function index()
    {
        $batches = ProductionBatch::with('category')->latest()->get();
        return inertia('ProductionBatches/Index', compact('batches'));
    }

    /**
     * Show the form for creating a new batch.
     */
    public function create()
    {
        $categories = Category::all();
        return inertia('ProductionBatches/Create', compact('categories'));
    }

    /**
     * Store a newly created batch in the database.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'batch_number' => 'required|unique:production_batches,batch_number',
            'quantity' => 'required|integer|min:1',
            'production_date' => 'required|date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        ProductionBatch::create($validated);
        return redirect()->route('productions.index')->with('success', 'Batch created successfully.');
    }

    /**
     * Show the form for editing a batch.
     */
    public function edit($id)
    {
        $productionBatch = ProductionBatch::findOrFail($id);
        $categories = Category::all();

        // dd($productionBatch, $categories);
        return Inertia::render('ProductionBatches/Edit', [
            'productionBatch' => $productionBatch,
            'categories' => $categories,
        ]);
    }

    /**
     * Update a batch in the database.
     */
    public function update(Request $request, $id)
    {
        // Retrieve the ProductionBatch manually
        $productionBatch = ProductionBatch::findOrFail($id);

        

        // Validate the request data
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'batch_number' => 'required|unique:production_batches,batch_number,' . $productionBatch->id,
            'quantity' => 'required|integer|min:1',
            'production_date' => 'required|date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        dd($validated);

        // Update the ProductionBatch
        $productionBatch->update($validated);

        // Redirect with success message
        return redirect()->route('productions.index')->with('success', 'Batch updated successfully.');
    }


    /**
     * Remove a batch from the database.
     */
    public function destroy(ProductionBatch $productionBatch)
    {
        $productionBatch->delete();
        return redirect()->route('production-batches.index')->with('success', 'Batch deleted successfully.');
    }
}
