<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Browse/search products. Returns JSON for now — this is the
     * endpoint a React frontend will fetch from; swap to
     * Inertia::render() later if you go that route instead, the query
     * logic underneath doesn't need to change either way.
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->with(['category', 'brand', 'defaultVariant']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('min_price')) {
            $minMinor = (float) $request->input('min_price') * 100;
            $query->whereHas('variants', fn ($q) => $q->where('price_minor', '>=', $minMinor));
        }

        if ($request->filled('max_price')) {
            $maxMinor = (float) $request->input('max_price') * 100;
            $query->whereHas('variants', fn ($q) => $q->where('price_minor', '<=', $maxMinor));
        }

        if ($request->boolean('in_stock_only')) {
            $query->whereHas('variants', fn ($q) => $q->where('stock_qty', '>', 0));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('JSON_EXTRACT(name, "$.ar") LIKE ?', ["%{$search}%"]);
            });
        }

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->join('product_variants', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->where('product_variants.is_default', true);
            })->orderBy('product_variants.price_minor', 'asc')->select('products.*'),
            'price_desc' => $query->join('product_variants', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->where('product_variants.is_default', true);
            })->orderBy('product_variants.price_minor', 'desc')->select('products.*'),
            'name' => $query->orderBy('slug'),
            default => $query->latest('published_at'),
        };

        return response()->json([
            'products' => $query->paginate(24),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'brand',
            'variants' => fn ($q) => $q->where('is_active', true),
            'variants.attributeValues.attribute',
        ]);

        return response()->json(['product' => $product]);
    }
}