<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One "Best-Selling Cartons for Retailers" card on the public Bloom page.
 *
 * The ERP is the source of truth; the frontsite mirrors these cards through
 * `erp:sync-bloom`. They are edited at /bloom-products in the admin.
 */
class BloomFeaturedProduct extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'pack_description',
        'price_per_carton',
        'retail_margin',
        'badge',
        'image_url',
        'cta_label',
        'cta_url',
        'is_highlighted',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_per_carton' => 'decimal:2',
        'retail_margin' => 'decimal:2',
        'is_highlighted' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product) {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    protected static function uniqueSlug(?string $name): string
    {
        $base = Str::slug((string) $name) ?: 'bloom-carton';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getPriceDisplayAttribute(): ?string
    {
        if ($this->price_per_carton === null) {
            return null;
        }

        return '₦' . number_format((float) $this->price_per_carton, 0);
    }

    public function getMarginDisplayAttribute(): ?string
    {
        if ($this->retail_margin === null) {
            return null;
        }

        return '₦' . number_format((float) $this->retail_margin, 0) . '+';
    }

    public function ctaHref(): string
    {
        $url = trim((string) $this->cta_url);

        return $url !== '' ? $url : 'https://bloom.zaheerah.com';
    }

    /**
     * Absolute image URL for the frontsite, mirroring how the catalogue API
     * exposes product images.
     */
    public function storefrontImageUrl(): ?string
    {
        return $this->image_url ? url($this->image_url) : null;
    }

    /**
     * Payload sent to the frontsite by the storefront API.
     *
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'pack_description' => $this->pack_description,
            'price_per_carton' => $this->price_per_carton !== null ? (float) $this->price_per_carton : null,
            'retail_margin' => $this->retail_margin !== null ? (float) $this->retail_margin : null,
            'badge' => $this->badge,
            'image_url' => $this->storefrontImageUrl(),
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'is_highlighted' => $this->is_highlighted,
            'sort_order' => $this->sort_order,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
