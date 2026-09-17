<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Production;
use DB;
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
        $validated['batch_number'] = 'BATCH-' . str_pad($batchId, 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // Create production batch
            $production = Production::create($validated);

            // If status is "Completed", update inventory
            if ($validated['batch_status'] === 'Completed') {
                $product = Product::find($validated['product_id']);

                // 1. Increase product stock
                $product->stock += $validated['quantity'];
                $product->save();

                // 2. Record inventory transaction (KEY FIX)
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $this->calculateUnitCost($production), // See helper below
                    'type' => 'production',
                    'batch_reference' => $production->batch_number,
                    'notes' => 'Batch ' . $production->batch_number . ' (' . $production->description . ')',

                ]);
            }

            DB::commit();

            log_activity('Production Created', 'Created production batch: ' . $production->batch_number);

            // Create the production batch

            return redirect()->route('productions.index')->with('success', 'Batch created successfully.');
        }// In store(), add rollback and error handling:
        catch (\Exception $e) {
            DB::rollBack(); // 👈 ADD THIS
            \Log::error("Production Creation Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to create batch. Check logs.');
        }
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
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'material_cost' => 'required|numeric',
            'labor_cost' => 'required|numeric',
            'overhead_cost' => 'required|numeric',
            'batch_status' => 'required|in:Pending,In Progress,Completed,Cancelled',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($validated['product_id']);

            // Case 1: Newly marked as "Completed"
            if ($validated['batch_status'] === 'Completed' && $production->batch_status !== 'Completed') {

                if ($product->stock < $production->quantity) {
                    throw new \Exception("Insufficient stock to revert this batch.");
                }
                // Update stock
                $product->stock += $validated['quantity'];
                $product->save();

                // Record inventory transaction (using batch_number as reference)
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $this->calculateUnitCost($production),
                    'type' => 'production',
                    'batch_reference' => $production->batch_number, // Add this field to inventory_transactions
                    'notes' => 'Batch ' . $production->batch_number . ' (' . $production->description . ')',
                ]);
            }

            // Case 2: Changed FROM "Completed"
            elseif ($production->batch_status === 'Completed' && $validated['batch_status'] !== 'Completed') {
                // Revert stock
                $product->stock -= $production->quantity;
                $product->save();

                // Delete the related transaction (using batch_number)
                InventoryTransaction::where('batch_reference', $production->batch_number)
                    ->where('type', 'production')
                    ->delete();
            }

            // Case 3: Quantity/cost changed while status remains "Completed"
            elseif ($production->batch_status === 'Completed' && $validated['batch_status'] === 'Completed') {
                $quantityDiff = $validated['quantity'] - $production->quantity;

                if ($quantityDiff != 0) {
                    $product->stock += $quantityDiff;
                    $product->save();

                    // Update existing transaction
                    InventoryTransaction::where('batch_reference', $production->batch_number)
                        ->where('type', 'production')
                        ->update([
                            'quantity' => $validated['quantity'],
                            'unit_cost' => $this->calculateUnitCost($production),
                        ]);
                }
            }

            // Update production record
            $production->update($validated);
            DB::commit();

            log_activity('Production Updated', 'Updated batch: ' . $production->batch_number);
            return redirect()->route('productions.index')->with('success', 'Batch updated!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Production Update Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to update batch. Check logs.');
        }
    }

    // Helper method



    /**
     * Remove a batch from the database.
     */
    // In destroy() method, add stock reversion:
    public function destroy(Production $production)
    {
        DB::beginTransaction();
        try {
            // Revert stock if batch was completed
            if ($production->batch_status === 'Completed') {
                $product = Product::find($production->product_id);
                $product->stock -= $production->quantity;
                $product->save();

                // Delete linked transaction
                InventoryTransaction::where('batch_reference', $production->batch_number)
                    ->delete();
            }

            $production->delete();
            DB::commit();

            log_activity('Production Deleted', 'Deleted batch: ' . $production->batch_number);
            return redirect()->route('productions.index')->with('success', 'Batch deleted!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Deletion Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to delete batch.');
        }
    }

    function calculateUnitCost(Production $production)
    {
        // Total production cost / quantity produced
        return ($production->material_cost + $production->labor_cost + $production->overhead_cost)
            / $production->quantity;
    }
}
