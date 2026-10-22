<?php

namespace App\Support;

use App\Models\Production;
use App\Models\Sale;

/**
 * Collision-resistant, human-readable sequential numbers.
 *
 * The old code used "max(id) + 1" on its own. Two concurrent requests could
 * compute the same value, and a deleted/last row could reuse a number. This
 * keeps the existing RC-000001 / BATCH-00001 shape but verifies the value is
 * actually unused before handing it out, and falls back to a random suffix
 * rather than ever returning a duplicate.
 */
final class SequentialNumber
{
    public static function receipt(): string
    {
        return self::next(Sale::class, 'receipt_number', 'RC-', 6);
    }

    public static function batch(): string
    {
        return self::next(Production::class, 'batch_number', 'BATCH-', 5);
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    private static function next(string $model, string $column, string $prefix, int $pad): string
    {
        $max = (int) $model::query()->max('id');

        for ($i = $max + 1; $i <= $max + 500; $i++) {
            $candidate = $prefix . str_pad((string) $i, $pad, '0', STR_PAD_LEFT);

            if (! $model::query()->where($column, $candidate)->exists()) {
                return $candidate;
            }
        }

        // Extremely unlikely: fall back to a random value instead of a clash.
        $ceiling = (10 ** $pad) - 1;

        do {
            $candidate = $prefix . str_pad((string) random_int(0, $ceiling), $pad, '0', STR_PAD_LEFT);
        } while ($model::query()->where($column, $candidate)->exists());

        return $candidate;
    }
}
