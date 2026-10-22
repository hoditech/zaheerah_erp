<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Production;
use App\Support\SequentialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use RuntimeException;
use Throwable;

class ProductionController extends Controller
{
    /**
     * Display a listing of production batches.
     */
    public function index()
    {
        $user = auth()->user();

        $batches = Production::with('product')
            ->when(! $user->seesAllCategories(), function ($query) use ($user) {
                $query->whereHas('product', function ($productQuery) use ($user) {
                    $productQuery->where('category_id', $user->category_id);
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
        $user = auth()->user();

        $products = Product::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('category_id', $user->category_id);
        })->get();

        return inertia('ProductionBatches/Create', compact('products'));
    }

    /**
     * Store a newly created batch in the database.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        try {
            $production = DB::transaction(function () use ($validated) {
                $validated['batch_number'] = SequentialNumber::batch();

                $production = Production::create($validated);

                // Only a completed batch puts finished units into stock.
                if ($validated['batch_status'] === 'Completed') {
                    $this->addStock($production, $validated);
                }

                return $production;
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to create batch: ' . $e->getMessage());
        }

        log_activity('Production Created', 'Created production batch: ' . $production->batch_number);

        return redirect()->route('productions.index')->with('success', 'Batch created successfully.');
    }

    /**
     * Show the form for editing a batch.
     */
    public function edit(Production $production)
    {
        $user = auth()->user();

        $products = Product::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('category_id', $user->category_id);
        })->get();

        return Inertia::render('ProductionBatches/Edit', [
            'production' => $production,
            'products' => $products,
        ]);
    }

    /**
     * Update a batch in the database.
     *
     * The stock movement is always: reverse what the old state added, then
     * apply what the new state adds. That single rule covers every transition
     * (pending -> completed, completed -> pending, completed -> completed with
     * a different product or quantity) and fixes three bugs in the old code:
     * stock being moved to the wrong product, the old product never being
     * reverted, and the ledger cost being read from the stale row.
     */
    public function update(Request $request, Production $production)
    {
        $validated = $this->validated($request);
        $batchNumber = $production->batch_number;

        try {
            DB::transaction(function () use ($production, $validated) {
                if ($production->batch_status === 'Completed') {
                    $this->removeStock($production);
                }

                $production->update($validated);

                if ($validated['batch_status'] === 'Completed') {
                    $this->addStock($production, $validated);
                }
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to update batch: ' . $e->getMessage());
        }

        log_activity('Production Updated', 'Updated batch: ' . $batchNumber);

        return redirect()->route('productions.index')->with('success', 'Batch updated!');
    }

    /**
     * Remove a batch from the database.
     */
    public function destroy(Production $production)
    {
        $batchNumber = $production->batch_number;

        try {
            DB::transaction(function () use ($production) {
                if ($production->batch_status === 'Completed') {
                    $this->removeStock($production);
                }

                $production->delete();
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to delete batch: ' . $e->getMessage());
        }

        log_activity('Production Deleted', 'Deleted batch: ' . $batchNumber);

        return redirect()->route('productions.index')->with('success', 'Batch deleted!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'material_cost' => 'required|numeric|min:0',
            'labor_cost' => 'required|numeric|min:0',
            'overhead_cost' => 'required|numeric|min:0',
            'batch_status' => 'required|in:Pending,In Progress,Completed,Cancelled',
        ]);
    }

    /**
     * Put a completed batch into stock and record it in the inventory ledger.
     *
     * @param  array<string, mixed>  $data
     */
    private function addStock(Production $production, array $data): void
    {
        $product = Product::lockForUpdate()->findOrFail($data['product_id']);

        $product->stock = (int) $product->stock + (int) $data['quantity'];
        $product->save();

        InventoryTransaction::create([
            'product_id' => $product->id,
            'quantity' => (int) $data['quantity'],
            'unit_cost' => $this->unitCost($data),
            'type' => 'production',
            'batch_reference' => $production->batch_number,
            'notes' => 'Batch ' . $production->batch_number . ' (' . ($data['description'] ?? '') . ')',
        ]);
    }

    /**
     * Take a completed batch back out of stock, refusing to go negative.
     */
    private function removeStock(Production $production): void
    {
        $product = Product::lockForUpdate()->find($production->product_id);

        if (! $product) {
            return;
        }

        if ((int) $product->stock < (int) $production->quantity) {
            throw new RuntimeException(
                "Cannot revert batch {$production->batch_number}: only {$product->stock} unit(s) of "
                . "{$product->name} are left in stock but the batch produced {$production->quantity}. "
                . 'Adjust the stock first so it never goes negative.'
            );
        }

        $product->stock = (int) $product->stock - (int) $production->quantity;
        $product->save();

        InventoryTransaction::where('batch_reference', $production->batch_number)
            ->where('type', 'production')
            ->delete();
    }

    /**
     * Unit cost for the ledger, based on the batch values being saved.
     *
     * @param  array<string, mixed>  $data
     */
    private function unitCost(array $data): float
    {
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        $total = (float) ($data['material_cost'] ?? 0)
            + (float) ($data['labor_cost'] ?? 0)
            + (float) ($data['overhead_cost'] ?? 0);

        return round($total / $quantity, 2);
    }
}
