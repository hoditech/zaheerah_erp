<?php

namespace App\Http\Controllers;

use App\Models\BloomFeaturedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Admin management for the "Best-Selling Cartons" cards on the public Bloom
 * page. The frontsite mirrors them through `erp:sync-bloom`.
 */
class BloomFeaturedProductController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Bloom/Products/Index', [
            'products' => BloomFeaturedProduct::ordered()->get(),
            'canManage' => (bool) $request->user()?->can('manage_bloom_pricing'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Bloom/Products/Create');
    }

    public function store(Request $request)
    {
        $product = BloomFeaturedProduct::create($this->validated($request));

        log_activity('Bloom Carton Created', 'Created Bloom carton card: ' . $product->name);

        return redirect()->route('bloom-products.index')->with('success', 'Bloom carton card created.');
    }

    public function edit(BloomFeaturedProduct $bloomProduct)
    {
        return Inertia::render('Bloom/Products/Edit', [
            'product' => $bloomProduct,
        ]);
    }

    public function update(Request $request, BloomFeaturedProduct $bloomProduct)
    {
        $bloomProduct->update($this->validated($request, $bloomProduct));

        log_activity('Bloom Carton Updated', 'Updated Bloom carton card: ' . $bloomProduct->name);

        return redirect()->route('bloom-products.index')->with('success', 'Bloom carton card updated.');
    }

    public function destroy(BloomFeaturedProduct $bloomProduct)
    {
        $name = $bloomProduct->name;
        $bloomProduct->delete();

        log_activity('Bloom Carton Deleted', 'Deleted Bloom carton card: ' . $name);

        return redirect()->route('bloom-products.index')->with('success', $name . ' deleted.');
    }

    /**
     * Validate and normalise the carton form into a bloom_featured_products row.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?BloomFeaturedProduct $product = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'pack_description' => 'nullable|string|max:120',
            'price_per_carton' => 'nullable|numeric|min:0|max:999999999',
            'retail_margin' => 'nullable|numeric|min:0|max:999999999',
            'badge' => 'nullable|string|max:40',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cta_label' => 'nullable|string|max:60',
            'cta_url' => 'nullable|string|max:2048',
            'is_highlighted' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
        ]);

        $data = $validator->validate();

        $uploadedImage = $this->storeImage($request);

        return [
            'name' => trim((string) $data['name']),
            'pack_description' => $this->nullableString($data['pack_description'] ?? null),
            'price_per_carton' => filled($data['price_per_carton'] ?? null) ? (float) $data['price_per_carton'] : null,
            'retail_margin' => filled($data['retail_margin'] ?? null) ? (float) $data['retail_margin'] : null,
            'badge' => $this->nullableString($data['badge'] ?? null),
            'image_url' => $uploadedImage ?? $product?->image_url,
            'cta_label' => $this->nullableString($data['cta_label'] ?? null) ?? 'Add to Order',
            'cta_url' => $this->nullableString($data['cta_url'] ?? null),
            'is_highlighted' => $request->boolean('is_highlighted'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->filled('sort_order')
                ? (int) $data['sort_order']
                : ($product?->sort_order ?? ((int) BloomFeaturedProduct::query()->max('sort_order')) + 1),
        ];
    }

    /**
     * Store an uploaded carton image on the public disk, in the same shape the
     * catalogue API uses ("storage/...").
     */
    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');

        $filename = time() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('bloom-products', $filename, 'public');

        return 'storage/' . $path;
    }

    private function nullableString($value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : null;
    }
}
