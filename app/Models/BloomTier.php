<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One "Bloom Tier Benefits" card.
 *
 * The ERP is the source of truth for these cards; the public frontsite mirrors
 * them through `erp:sync-bloom`. They are edited at /bloom-tiers in the admin.
 */
class BloomTier extends Model
{
    public const THEMES = [
        'cream' => 'Cream metallic',
        'silver' => 'Silver metallic (elevated)',
        'platinum' => 'Platinum metallic',
    ];

    protected $fillable = [
        'slug',
        'name',
        'subtitle',
        'icon',
        'min_cartons',
        'max_cartons',
        'range_label',
        'unit_label',
        'price_per_carton',
        'discount_percent',
        'benefits',
        'cta_label',
        'cta_url',
        'footnote',
        'badge',
        'theme',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_cartons' => 'integer',
        'max_cartons' => 'integer',
        'price_per_carton' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $tier) {
            if (blank($tier->slug)) {
                $tier->slug = static::uniqueSlug($tier->name);
            }
        });
    }

    protected static function uniqueSlug(?string $name): string
    {
        $base = Str::slug((string) $name) ?: 'bloom-tier';
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

    /**
     * The volume band, e.g. "100–199", "500+", or a custom label.
     */
    public function getRangeDisplayAttribute(): string
    {
        if (filled($this->range_label)) {
            return $this->range_label;
        }

        if ($this->min_cartons !== null && $this->max_cartons !== null) {
            return $this->min_cartons . '–' . $this->max_cartons;
        }

        if ($this->min_cartons !== null) {
            return $this->min_cartons . '+';
        }

        return '';
    }

    public function getPriceDisplayAttribute(): ?string
    {
        if ($this->price_per_carton === null) {
            return null;
        }

        return '₦' . number_format((float) $this->price_per_carton, 0);
    }

    public function getDiscountDisplayAttribute(): ?string
    {
        if ($this->discount_percent === null) {
            return null;
        }

        $formatted = rtrim(rtrim(number_format((float) $this->discount_percent, 2, '.', ''), '0'), '.');

        return $formatted . '% off';
    }

    public function ctaHref(): string
    {
        $url = trim((string) $this->cta_url);

        return $url !== '' ? $url : 'https://bloom.zaheerah.com';
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
            'subtitle' => $this->subtitle,
            'icon' => $this->icon,
            'min_cartons' => $this->min_cartons,
            'max_cartons' => $this->max_cartons,
            'range_label' => $this->range_label,
            'unit_label' => $this->unit_label,
            'price_per_carton' => $this->price_per_carton !== null ? (float) $this->price_per_carton : null,
            'discount_percent' => $this->discount_percent !== null ? (float) $this->discount_percent : null,
            'benefits' => $this->benefits ?? [],
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'footnote' => $this->footnote,
            'badge' => $this->badge,
            'theme' => $this->theme,
            'sort_order' => $this->sort_order,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
