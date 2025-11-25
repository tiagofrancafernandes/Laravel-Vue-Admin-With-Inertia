<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sort_val',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_val' => 'integer',
    ];

    protected $appends = [
        'salesCount',
    ];

    public function scopeOrderByPopularity($query)
    {
        return $query
            ->orderBy('sort_val', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function getSalesCountAttribute(): int
    {
        return $this->getPopularity();
    }

    public function getPopularity(): int
    {
        return static::getProductPopularity($this->{'name'} ?? '');
    }

    public static function getProductPopularity(string $productName, bool $renewCache = false): int
    {
        $productName = trim($productName);

        if (!$productName) {
            return 0;
        }

        $cacheKey = __METHOD__ . $productName;

        if ($renewCache) {
            cache()->forget($cacheKey);
        }

        return cache()->remember(
            $cacheKey,
            60,
            function () use ($productName) {
                $sales = Sale::all();
                $count = 0;

                foreach ($sales as $sale) {
                    if (is_string($sale->items)) {
                        $items = json_decode($sale->items, true);
                    } else {
                        $items = $sale->items ?? [];
                    }

                    foreach ($items as $item) {
                        if (isset($item['name']) && $item['name'] === $productName) {
                            $count++;
                        }
                    }
                }

                return $count;
            }
        );
    }
}
