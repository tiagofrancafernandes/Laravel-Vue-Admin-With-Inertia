<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    public function select(Request $request)
    {
        $str = fn ($v) => filter_var($v, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);
        $search = $str($request->input('q') ?? $request->input('search'));

        $limit = $request->integer('limit', 10);
        $offset = $request->integer('offset', 0);

        $search = trim("{$search}");

        $limit = $limit > 0 ? $limit : 10;
        $offset = $offset >= 0 ? $offset : 10;

        $cacheKey = implode('-', [
            __METHOD__,
            $limit,
            $offset,
            $search,
        ]);

        $products = cache()->remember($cacheKey, 300, function () use ($limit, $offset, $search) {
            return Product::query()
                ->orderByPopularity()
                ->limit($limit)
                ->offset($offset)
                ->when($search, function (Builder $query, string $_search) {
                    $_search = '%' . strtolower($_search) . '%';

                    $query
                        ->whereRaw('LOWER(name) LIKE ?', [$_search])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$_search]);
                })
                ->get()
                // ->map(function ($product) {
                //     $product->sales_count = Product::getProductPopularity($product->name);
                //     return $product;
                // })
                // ->sortByDesc('sort_val', SORT_DESC)
                // ->sortByDesc('sort_val', SORT_ASC)
                // ->sortByDesc('sales_count', SORT_ASC)
                ->values();
        });

        return response()->json([
            'data' => $products,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
