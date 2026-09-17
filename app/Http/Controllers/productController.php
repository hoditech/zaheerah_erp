<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use DB;
use Illuminate\Http\Request;
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
        $categoryId = auth()->user()->category_id;
        $products = Product::with('category', 'supplier')
            ->when($categoryId != 1, function ($query) use ($categoryId) {
                // Filter by category_id if it is not 1
                $query->where('category_id', $categoryId);
            })
            ->get();

       // dd($products);
        // Debugbar::info($products);

        return Inertia::render('product/ProductPage', props: [
            'products' => $products,
        ]);


    }


    public function create()
    {
        $categoryId = auth()->user()->category_id;


        $categories = Category::when($categoryId != 1, function ($query) use ($categoryId) {
            // Fetch only the user's category if their category_id is not 1
            $query->where('id', $categoryId);
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
            $product = Product::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'unit_cost' => $validated['unit_cost'],
                'stock' => $validated['initial_stock'], // Set initial stock
                'category_id' => $validated['category_id'],
                'image_url' => $this->storeImage($request),
                // Optional fields
                'weight_per_unit' => $validated['weight_per_unit'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'exp_date' => $validated['exp_date'] ?? null,
                'batch_number' => $validated['batch_number'] ?? null,
                'storage_location' => $validated['storage_location'] ?? null,
            ]);
    
            // Record inventory transaction
            InventoryTransaction::create([
                'product_id' => $product->id,
                'quantity' => $validated['initial_stock'],
                'unit_cost' => $validated['unit_cost'],
                'type' => 'initial',
            ]);
    
            log_activity('Product Created', 'Created product: ' . $product->name);
    
            return redirect()->route('products')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            \Log::error("Product Store Error: " . $e->getMessage());
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
            'suppliers' => $suppliers
        ]);

    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
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
                // Adjust stock if needed
                if (isset($validated['stock_adjustment'])) {
                    $product->stock += $validated['stock_adjustment'];
    
                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'quantity' => $validated['stock_adjustment'],
                        'unit_cost' => $validated['unit_cost'],
                        'type' => $validated['stock_adjustment'] > 0 ? 'restock' : 'deduction',
                    ]);
                }
    
                // Update product. The image is only replaced when a new file is
                // uploaded, otherwise the existing one is kept.
                $product->update([
                    'name' => $validated['name'],
                    'price' => $validated['price'],
                    'unit_cost' => $validated['unit_cost'],
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
            \Log::error("Product Update Error: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update product. Please try again.']);
        }
    }
    


    public function destroy(Product $product)
    {
        $productName = $product->name;
        $product->delete();
        // Log activity for product deletion
        log_activity('Product Deleted', 'Deleted product: ' . $productName);
        return redirect()->route('products')->with('success', 'Product deleted successfully.');
    }

}
