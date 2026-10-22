<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class productController extends Controller
{
    /**
     * Validation rules shared by create and edit, including the product image.
     *
     * @return array<string, string>
     */
    private function imageRules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    /**
     * Store an uploaded product image on the public disk.
     *
     * Saves the same shape the storefront uses ("storage/products/x.jpg") so the
     * shop can take the value straight from the catalogue API. Returns null when
     * no file was uploaded.
     */
    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');

        $filename = time() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('products', $filename, 'public');

        return 'storage/' . $path;
    }

    public function index()
    {
        $user = auth()->user();

        $products = Product::with('category', 'supplier')
            ->when(! $user->seesAllCategories(), function ($query) use ($user) {
                $query->where('category_id', $user->category_id);
            })
            ->get();

        return Inertia::render('product/ProductPage', props: [
            'products' => $products,
        ]);
    }


    public function create()
    {
        $user = auth()->user();

        $categories = Category::when(! $user->seesAllCategories(), function ($query) use ($user) {
            $query->where('id', $user->category_id);
        })->get();

        $suppliers = Supplier::all();

        return Inertia::render('product/Create', [
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'initial_stock' => 'required|integer|min:0', // First stock input
            'category_id' => 'required|exists:categories,id',
            'weight_per_unit' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'exp_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:255',
        ] + $this->imageRules());

        try {
            $product = DB::transaction(function () use ($validated, $request) {
                $product = Product::create([
                    'name' => $validated['name'],
                    'price' => $validated['price'],
                    'unit_cost' => $validated['unit_cost'],
                    'stock' => $validated['initial_stock'],
                    'category_id' => $validated['category_id'],
                    'image_url' => $this->storeImage($request),
                    'weight_per_unit' => $validated['weight_per_unit'] ?? null,
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'exp_date' => $validated['exp_date'] ?? null,
                    'batch_number' => $validated['batch_number'] ?? null,
                    'storage_location' => $validated['storage_location'] ?? null,
                ]);

                // Only log opening stock when there actually is some. A zero
                // quantity ledger row is meaningless noise.
                if ((int) $validated['initial_stock'] > 0) {
                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'quantity' => $validated['initial_stock'],
                        'unit_cost' => $validated['unit_cost'],
                        'type' => 'initial',
                    ]);
                }

                return $product;
            });

            log_activity('Product Created', 'Created product: ' . $product->name);

            return redirect()->route('products')->with('success', 'Product created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'Failed to create product. Please try again.']);
        }
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::all();
        $categories = Category::all();

        return Inertia::render('product/Edit', [
            'product' => $product,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // Optional on edit: the seeded catalogue was imported without costs,
            // so requiring it here made every stock adjustment impossible.
            'unit_cost' => 'nullable|numeric|min:0',
            'stock_adjustment' => 'nullable|integer',
            'category_id' => 'required|exists:categories,id',
            'weight_per_unit' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'exp_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:255',
        ] + $this->imageRules());

        try {
            DB::transaction(function () use ($validated, $product, $request) {
                $product = Product::lockForUpdate()->findOrFail($product->id);

                // Keep the stored cost when the form leaves the field blank, but
                // fall back to 0 for the stock ledger: inventory_transactions
                // requires a unit_cost, and the product may not have one yet.
                $unitCost = $validated['unit_cost'] ?? $product->unit_cost;

                // Adjust stock if needed. The ledger must record the movement
                // that was actually applied, not the raw request, or a clamped
                // adjustment leaves the ledger disagreeing with the stock row.
                $adjustment = (int) ($validated['stock_adjustment'] ?? 0);

                if ($adjustment !== 0) {
                    $current = (int) $product->stock;
                    $target = max(0, $current + $adjustment);
                    $applied = $target - $current;

                    if ($applied !== 0) {
                        $product->stock = $target;

                        InventoryTransaction::create([
                            'product_id' => $product->id,
                            'quantity' => $applied,
                            'unit_cost' => $unitCost ?? 0,
                            'type' => $applied > 0 ? 'restock' : 'deduction',
                        ]);
                    }
                }

                // Update product. The image is only replaced when a new file is
                // uploaded, otherwise the existing one is kept.
                $product->update([
                    'name' => $validated['name'],
                    'price' => $validated['price'],
                    'unit_cost' => $unitCost,
                    'category_id' => $validated['category_id'],
                    'image_url' => $request->hasFile('image')
                        ? $this->storeImage($request)
                        : $product->image_url,
                    'weight_per_unit' => $validated['weight_per_unit'] ?? $product->weight_per_unit,
                    'supplier_id' => $validated['supplier_id'] ?? $product->supplier_id,
                    'exp_date' => $validated['exp_date'] ?? $product->exp_date,
                    'batch_number' => $validated['batch_number'] ?? $product->batch_number,
                    'storage_location' => $validated['storage_location'] ?? $product->storage_location,
                ]);
            });

            log_activity('Product Updated', 'Updated product: ' . $product->name);

            return redirect()->route('products')->with('success', 'Product updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'Failed to update product. Please try again.']);
        }
    }


    public function destroy(Product $product)
    {
        $productName = $product->name;

        // A delete used to either fail with a raw SQL foreign-key 500 (product
        // has sales) or silently cascade away production/inventory history.
        // Refuse it explicitly instead, so history is never destroyed.
        $hasSales = $product->saleItems()->exists();
        $hasProduction = $product->productions()->exists();
        $hasStockMovement = $product->inventoryTransactions()
            ->whereNotIn('type', ['initial'])
            ->exists();

        if ($hasSales || $hasProduction || $hasStockMovement) {
            return back()->withErrors([
                'error' => "Cannot delete {$productName}: it has sales, production or stock-movement history. "
                    . 'Keep the product for the audit trail instead.',
            ]);
        }

        try {
            DB::transaction(function () use ($product) {
                // Only the opening-stock rows are left; they belong to this row.
                $product->inventoryTransactions()->delete();
                $product->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'Failed to delete product. Please try again.']);
        }

        log_activity('Product Deleted', 'Deleted product: ' . $productName);

        return redirect()->route('products')->with('success', 'Product deleted successfully.');
    }
}
