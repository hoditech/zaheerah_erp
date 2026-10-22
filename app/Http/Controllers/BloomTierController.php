<?php

namespace App\Http\Controllers;

use App\Models\BloomTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

/**
 * Admin management for the "Bloom Tier Benefits" cards.
 *
 * These are shown on the public frontsite at /zaheerah-bloom. The frontsite
 * mirrors them through `erp:sync-bloom`, so editing here is what changes the
 * live Bloom page.
 */
class BloomTierController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Bloom/Tiers/Index', [
            'tiers' => BloomTier::ordered()->get(),
            'canManage' => (bool) $request->user()?->can('manage_bloom_pricing'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Bloom/Tiers/Create', [
            'themes' => BloomTier::THEMES,
        ]);
    }

    public function store(Request $request)
    {
        $tier = BloomTier::create($this->validated($request));

        log_activity('Bloom Tier Created', 'Created Bloom tier: ' . $tier->name);

        return redirect()->route('bloom-tiers.index')->with('success', 'Bloom tier created.');
    }

    public function edit(BloomTier $bloomTier)
    {
        return Inertia::render('Bloom/Tiers/Edit', [
            'tier' => $bloomTier,
            'themes' => BloomTier::THEMES,
        ]);
    }

    public function update(Request $request, BloomTier $bloomTier)
    {
        $bloomTier->update($this->validated($request, $bloomTier));

        log_activity('Bloom Tier Updated', 'Updated Bloom tier: ' . $bloomTier->name);

        return redirect()->route('bloom-tiers.index')->with('success', 'Bloom tier updated.');
    }

    public function destroy(BloomTier $bloomTier)
    {
        $name = $bloomTier->name;
        $bloomTier->delete();

        log_activity('Bloom Tier Deleted', 'Deleted Bloom tier: ' . $name);

        return redirect()->route('bloom-tiers.index')->with('success', $name . ' deleted.');
    }

    /**
     * Validate and normalise the tier form into a bloom_tiers row.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?BloomTier $tier = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:60',
            'subtitle' => 'nullable|string|max:60',
            'icon' => 'nullable|string|max:16',
            'min_cartons' => 'nullable|integer|min:0|max:1000000',
            'max_cartons' => 'nullable|integer|min:0|max:1000000',
            'range_label' => 'nullable|string|max:40',
            'unit_label' => 'nullable|string|max:20',
            'price_per_carton' => 'nullable|numeric|min:0|max:999999999',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'benefits' => 'nullable|string|max:2000',
            'cta_label' => 'nullable|string|max:60',
            'cta_url' => 'nullable|string|max:2048',
            'footnote' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:40',
            'theme' => 'required|in:' . implode(',', array_keys(BloomTier::THEMES)),
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $min = $request->input('min_cartons');
            $max = $request->input('max_cartons');

            if (filled($min) && filled($max) && (int) $max < (int) $min) {
                $validator->errors()->add(
                    'max_cartons',
                    'The maximum carton count must be greater than or equal to the minimum.'
                );
            }
        });

        $data = $validator->validate();

        return [
            'name' => trim((string) $data['name']),
            'subtitle' => $this->nullableString($data['subtitle'] ?? null),
            'icon' => $this->nullableString($data['icon'] ?? null),
            'min_cartons' => filled($data['min_cartons'] ?? null) ? (int) $data['min_cartons'] : null,
            'max_cartons' => filled($data['max_cartons'] ?? null) ? (int) $data['max_cartons'] : null,
            'range_label' => $this->nullableString($data['range_label'] ?? null),
            'unit_label' => $this->nullableString($data['unit_label'] ?? null) ?? 'cartons',
            'price_per_carton' => filled($data['price_per_carton'] ?? null) ? (float) $data['price_per_carton'] : null,
            'discount_percent' => filled($data['discount_percent'] ?? null) ? (float) $data['discount_percent'] : null,
            'benefits' => $this->parseBenefits($request->input('benefits')),
            'cta_label' => $this->nullableString($data['cta_label'] ?? null) ?? 'Select Plan',
            'cta_url' => $this->nullableString($data['cta_url'] ?? null),
            'footnote' => $this->nullableString($data['footnote'] ?? null),
            'badge' => $this->nullableString($data['badge'] ?? null),
            'theme' => $data['theme'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->filled('sort_order')
                ? (int) $data['sort_order']
                : ($tier?->sort_order ?? ((int) BloomTier::query()->max('sort_order')) + 1),
        ];
    }

    /**
     * @return list<string>
     */
    private function parseBenefits(?string $raw): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_unique(array_filter(
            array_map(fn (string $line) => trim($line), $lines),
            fn (string $line) => $line !== ''
        )));
    }

    private function nullableString($value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : null;
    }
}
