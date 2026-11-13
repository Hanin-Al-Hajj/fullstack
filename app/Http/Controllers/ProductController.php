<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Columns are prefixed with "products." because the price sorts
        // join product_variants, which has columns with the same names.
        $query = Product::query()
            ->where('products.is_active', true)
            ->whereNotNull('products.published_at')
            ->with(['defaultVariant', 'media']);

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->string('category'))->first();
            // Include descendants so a parent category lists its children's products too.
            $ids = $category ? $category->descendants()->pluck('id')->push($category->id) : collect([0]);
            $query->whereIn('products.category_id', $ids);
        }

        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('min_price')) {
            $min = (float) $request->input('min_price') * 100;
            $query->whereHas('variants', fn ($q) => $q->where('price_minor', '>=', $min));
        }

        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price') * 100;
            $query->whereHas('variants', fn ($q) => $q->where('price_minor', '<=', $max));
        }

        if ($request->boolean('in_stock_only')) {
            $query->whereHas('variants', fn ($q) => $q->where('stock_qty', '>', 0));
        }

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q
                ->whereRaw('JSON_EXTRACT(products.name, "$.en") LIKE ?', [$term])
                ->orWhereRaw('JSON_EXTRACT(products.name, "$.ar") LIKE ?', [$term]));
        }

        $sort = $request->string('sort')->toString();

        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $query->join('product_variants', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->where('product_variants.is_default', true);
            })
                ->orderBy('product_variants.price_minor', $sort === 'price_asc' ? 'asc' : 'desc')
                ->select('products.*');
        } elseif ($sort === 'name') {
            $query->orderBy('products.slug');
        } else {
            $query->latest('products.published_at');
        }

        $pricing = app(PricingService::class);

        $products = $query->paginate(24)->withQueryString()->through(fn (Product $p) => [
            'id' => $p->id,
            'slug' => $p->slug,
            'name' => $p->name,
            'image_url' => $p->getFirstMediaUrl('gallery', 'thumb') ?: null,
            'default_variant' => $p->defaultVariant ? $pricing->display($p->defaultVariant) : null,
        ]);

                $seoCat = $request->filled('category')
            ? Category::where('slug', $request->string('category'))->first()
            : null;

        Seo::set([
            'title' => $seoCat ? Seo::t($seoCat->name) : Seo::tr('Shop', 'المتجر'),
            'url' => $seoCat ? url('/shop?category='.$seoCat->slug) : url('/shop'),
            'noindex' => $request->hasAny(['search', 'brand_id', 'min_price', 'max_price', 'in_stock_only', 'sort', 'page']),
        ]);

        return Inertia::render('Shop', [
            'products' => $products,
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', ''),
                'brand_id' => $request->input('brand_id', ''),
                'min_price' => $request->input('min_price', ''),
                'max_price' => $request->input('max_price', ''),
                'in_stock_only' => $request->boolean('in_stock_only'),
                'sort' => $request->input('sort', 'newest'),
            ],
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active && $product->published_at, 404);

        $product->load([
            'category',
            'brand',
            'media',
            'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default'),
            'variants.attributeValues',
        ]);

        $pricing = app(PricingService::class);


                $name = Seo::t($product->name);
        $first = $product->variants->first();
        $price = $first ? $pricing->display($first)['price_minor'] : null;
        $image = Seo::abs($product->getFirstMediaUrl('gallery'));

        $jsonld = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => strip_tags(Seo::t($product->short_description) ?: Seo::t($product->description)),
            'image' => $image,
            'sku' => $first?->sku,
        ];
        if ($product->brand) {
            $jsonld['brand'] = ['@type' => 'Brand', 'name' => Seo::t($product->brand->name)];
        }
        if ($first && $price !== null) {
            $jsonld['offers'] = [
                '@type' => 'Offer',
                'url' => url()->current(),
                'priceCurrency' => $first->currency,
                'price' => number_format($price / 100, 2, '.', ''),
                'availability' => $product->variants->contains(fn ($v) => $v->inStock())
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ];
        }

        Seo::set([
            'title' => $name,
            'description' => Seo::t($product->short_description) ?: Seo::t($product->description),
            'image' => $image,
            'type' => 'product',
            'jsonld' => $jsonld,
        ]);

        return Inertia::render('Product', [
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'brand' => $product->brand?->name,
                'images' => $product->getMedia('gallery')->map(fn ($m) => $m->hasGeneratedConversion('large') ? $m->getUrl('large') : $m->getUrl())->values(),
                'variants' => $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'price_minor' => $pricing->display($v)['price_minor'],
                    'compare_at_price_minor' => $pricing->display($v)['compare_at_price_minor'],
                    'currency' => $v->currency,
                    'stock_qty' => $v->stock_qty,
                    'in_stock' => $v->inStock(),
                    'low_stock' => $v->isLowStock(),
                    'options' => $v->attributeValues->pluck('value')->values(),
                ])->values(),
            ],
        ]);
    }
}
