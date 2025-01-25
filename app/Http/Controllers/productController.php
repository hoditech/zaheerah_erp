<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class productController extends Controller
{
    public function index()
    {
        $categoryId = auth()->user()->category_id;
        $products = Product::with('category')
        ->when($categoryId != 1, function ($query) use ($categoryId) {
            // Filter by category_id if it is not 1
            $query->where('category_id', $categoryId);
        })
        ->get();
        //dd($products);
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
        return Inertia::render('product/Create',[
            'categories' => $categories
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'weight_per_bag' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'stock_receive' => 'nullable|integer|min:0',
            'stock_release' => 'nullable|integer|min:0',
            'remaining_bags' => 'nullable|integer|min:0',
            //'supplier_id' => 'nullable|exists:suppliers,id',
            'exp_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        //dd($validated);
    
        try {
            Product::create($validated);
    
            // Flash success message
            return back()->with('message', 'Product created successfully.');
        } catch (\Exception $e) {
            dd($e);
            return back()->withErrors(['error' => 'Failed to create product. Please try again.']);
        }
    }
    


    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('message', 'Product deleted successfully.');
    }

}
