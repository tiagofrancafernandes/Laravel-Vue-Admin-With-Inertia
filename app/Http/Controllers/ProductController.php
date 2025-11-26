<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of products with pagination.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('creator');

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');

            if ($status === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where('stock', '=', 0);
            }
        }

        $products = $query->paginate(15)->appends($request->query());

        return Inertia::render('Resources/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $request->input('search'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'stock_status' => $request->input('stock_status'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(Request $request)
    {
        return Inertia::render('Resources/Products/Create', [
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['product' => $product], 201);
        }

        return redirect()->route('products.show', $product)
            ->with('message', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product, Request $request)
    {
        return Inertia::render('Resources/Products/Show', [
            'product' => $product->load('creator'),
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product, Request $request)
    {
        return Inertia::render('Resources/Products/Edit', [
            'product' => $product,
            'pageType' => $request->query('type', 'page'),
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['product' => $product]);
        }

        return redirect()->route('products.show', $product)
            ->with('message', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Product deleted successfully.');
    }
}
