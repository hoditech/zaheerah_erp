<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Read/write access to the product catalogue for the public storefront.
 *
 * The ERP is the single source of truth for what exists, what it costs and how
 * much is left. The storefront keeps its own marketing copy and matches rows
 * through erp_product_id.
 */
class CatalogController extends Controller
{
    /**
     * How long to wait when pulling an image in, and the largest file to accept.
     */
    private const IMAGE_TIMEOUT = 15;

    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    /**
     * GET /api/v1/catalog/products
     *
     * ?updated_since=ISO8601 limits the response for incremental syncs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->orderBy('id');

        // Accept the storefront's ISO timestamp but never 422 an incremental
        // sync on a malformed value: fall back to a full catalogue instead.
        if ($sinceParam = $request->query('updated_since')) {
            try {
                // A raw "+" in a query string arrives as a space.
                $since = Carbon::parse(str_replace(' ', '+', (string) $sinceParam));
                $query->where('updated_at', '>=', $since);
            } catch (Throwable) {
                // Ignore the filter.
            }
        }

        $products = $query->get()->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            // Absolute so the storefront can use it directly. url() leaves an
            // already-absolute value (an image imported from the shop) alone.
            'image_url' => $product->image_url ? url($product->image_url) : null,
            'updated_at' => $product->updated_at?->toIso8601String(),
        ]);

        return response()->json([
            'data' => $products,
            'meta' => [
                'count' => $products->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/v1/catalog/products
     *
     * Creates or updates a product by name. Used once to import the products
     * that already exist on the storefront, so the ERP owns the whole catalogue.
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'external_ref' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $categoryId = null;

        if (! empty($validated['category'])) {
            $categoryId = Category::firstOrCreate(
                ['name' => $validated['category']],
                ['description' => 'Imported from the storefront catalogue']
            )->id;
        }

        $name = trim($validated['name']);

        // Match case-insensitively: a re-import of "Palm Oil" must update the
        // existing row, not create a second "palm oil" one.
        $product = Product::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? new Product();
        $created = ! $product->exists;

        if ($created) {
            $product->name = $name;
        }

        $product->price = $validated['price'];

        if (array_key_exists('stock', $validated) && $validated['stock'] !== null) {
            $product->stock = $validated['stock'];
        } elseif ($created) {
            $product->stock = 0;
        }

        if (! empty($validated['unit_cost'])) {
            $product->unit_cost = $validated['unit_cost'];
        }

        if ($categoryId) {
            $product->category_id = $categoryId;
        }

        // Bring the image into this ERP rather than linking back to wherever it
        // came from, so an imported product is complete on its own. Only set the
        // image when one is supplied, so a shop-side sync cannot wipe an image
        // that was uploaded in the admin.
        [$imagePath, $imageWarning] = $this->importImage($validated['image_url'] ?? null);

        if ($imagePath !== null) {
            $product->image_url = $imagePath;
        }

        $product->save();

        // Opening stock is only ever logged once, when the product is born.
        if ($created && $product->stock > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'quantity' => $product->stock,
                'unit_cost' => $product->unit_cost ?? 0,
                'type' => 'initial',
                'batch_reference' => $product->batch_number ?? 'IMPORT-' . $product->id,
                'notes' => 'Opening stock imported from the storefront catalogue',
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'category' => $product->category?->name,
                'image_url' => $product->image_url ? url($product->image_url) : null,
                'created' => $created,
            ],
            'warnings' => array_values(array_filter([$imageWarning])),
        ], $created ? 201 : 200);
    }

    /**
     * Store a copy of the given image on this ERP's public disk.
     *
     * Accepts either a path this ERP already owns, or an absolute URL to fetch.
     * Fetching means an imported product survives the other application going
     * away, which is the point of moving the catalogue here.
     *
     * @return array{0: string|null, 1: string|null} the stored value and any warning
     */
    private function importImage(?string $url): array
    {
        if ($url === null || trim($url) === '') {
            return [null, null];
        }

        // Already a relative path on this ERP's own disk.
        if (! str_starts_with($url, 'http')) {
            return [$url, null];
        }

        // An absolute URL pointing at this ERP: store the path, do not re-fetch.
        if ($this->isOwnHost($url)) {
            return [ltrim((string) parse_url($url, PHP_URL_PATH), '/'), null];
        }

        // Never let the shared secret turn this endpoint into an SSRF probe of
        // the internal network (cloud metadata, localhost services, ...).
        if (! $this->isSafeRemoteUrl($url)) {
            return [$url, "The image URL {$url} was refused (only public http/https hosts are allowed). The link was kept instead."];
        }

        try {
            $response = Http::timeout(self::IMAGE_TIMEOUT)->get($url);
        } catch (Throwable $e) {
            return [$url, "Could not download the product image from {$url}: {$e->getMessage()}"];
        }

        if ($response->failed()) {
            return [$url, "Could not download the product image from {$url} (HTTP {$response->status()}). The link was kept instead."];
        }

        $contentType = (string) $response->header('Content-Type');

        if (! str_starts_with($contentType, 'image/')) {
            return [$url, "The file at {$url} is not an image ({$contentType}). The link was kept instead."];
        }

        $body = $response->body();

        if (strlen($body) > self::MAX_IMAGE_BYTES) {
            return [$url, "The image at {$url} is larger than " . (self::MAX_IMAGE_BYTES / 1024 / 1024) . ' MB. The link was kept instead.'];
        }

        $filename = $this->imageFilename($url, $contentType);

        Storage::disk('public')->put('products/' . $filename, $body);

        return ['storage/products/' . $filename, null];
    }

    private function isOwnHost(string $url): bool
    {
        $ownHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $ownHost !== null && parse_url($url, PHP_URL_HOST) === $ownHost;
    }

    /**
     * Reject anything that is not a plain public http(s) URL.
     */
    private function isSafeRemoteUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(trim((string) ($parts['host'] ?? '')));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }

        // Literal IPs must be public; this blocks loopback, RFC1918 and cloud
        // metadata addresses such as 169.254.169.254.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        return true;
    }

    private function imageFilename(string $url, string $contentType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
        ];

        $base = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
        $base = Str::slug($base) ?: 'image';

        // Trust the content type first, then the URL's own extension.
        $extension = $extensions[$contentType]
            ?? strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION))
            ?: 'jpg';

        return time() . '-' . $base . '.' . $extension;
    }
}
