<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class productController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        //dd($products);
        Debugbar::info($products);

        return Inertia::render('product/ProductPage', props: [
            'products' => $products,
        ]);


    }


    public function create()
    {
        $categories = Category::all();
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
            'category_id' => 'required|exists:categories,id',
        ]);
    
        try {
            Product::create($validated);
    
            // Flash success message
            return back()->with('message', 'Product created successfully.');
        } catch (\Exception $e) {
            // Handle unexpected errors
            return back()->withErrors(['error' => 'Failed to create product. Please try again.']);
        }
    }
    


    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('message', 'Product deleted successfully.');
    }

}
