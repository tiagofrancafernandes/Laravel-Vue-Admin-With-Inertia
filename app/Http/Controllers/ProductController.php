<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function select(Request $request)
    {
        $products = Product::query()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                $product->sales_count = $this->getProductPopularity($product->name);
                return $product;
            })
            ->sortByDesc('sales_count')
            ->values();

        return response()->json([
            'data' => $products,
        ]);
    }

    private function getProductPopularity(string $productName): int
    {
        $sales = Sale::all();
        $count = 0;

        foreach ($sales as $sale) {
            if (is_string($sale->items)) {
                $items = json_decode($sale->items, true);
            } else {
                $items = $sale->items ?? [];
            }

            foreach ($items as $item) {
                if (isset($item['description']) && $item['description'] === $productName) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
