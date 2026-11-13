<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Promotion;
use App\Support\Seo;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $now = now();

        $banners = Banner::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')
            ->with('media')
            ->get()
            ->map(fn (Banner $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'link_url' => $b->link_url,
                'image_url' => $b->getFirstMediaUrl('banner') ?: null,
            ]);

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('_lft')
            ->get()
            ->map(fn (Category $c) => $c->only(['slug', 'name']));

        // Flash sale: the live promotion that ends soonest.
        $promotion = Promotion::where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->orderBy('ends_at')
            ->first();

        $flash = null;
        if ($promotion) {
            $flash = [
                'name' => $promotion->name,
                'ends_at' => $promotion->ends_at->toIso8601String(),
                'products' => $this->published($promotion->products())
                    ->limit(8)->get()
                    ->map(fn (Product $p) => $this->card($p, $promotion))->values(),
            ];
        }

        $featuredCollection = Collection::where('type', 'featured')->where('is_active', true)->first();
        $featured = $featuredCollection
            ? $this->published($featuredCollection->products())
                ->orderBy('collection_product.sort_order')
                ->limit(8)->get()
                ->map(fn (Product $p) => $this->card($p))->values()
            : collect();

        $newArrivals = $this->published(Product::query())
            ->latest('products.published_at')
            ->limit(8)->get()
            ->map(fn (Product $p) => $this->card($p))->values();

        

                Seo::set([
                        'title' => Seo::tr('Home', 'الرئيسية'),
        ]);    
        return Inertia::render('Home', [
            'banners' => $banners,
            'categories' => $categories,
            'flash' => $flash,
            'featured' => $featured,
            'newArrivals' => $newArrivals,
        ]);
    }

    private function published($query)
    {
        return $query
            ->where('products.is_active', true)
            ->whereNotNull('products.published_at')
            ->with(['defaultVariant', 'media']);
    }

    // Same shape as the shop cards. With a promotion, the sale price is shown
    // and the normal price becomes the struck-through one.
    private function card(Product $p, ?Promotion $promo = null): array
    {
        $v = $p->defaultVariant;
        $variant = $v?->only(['id', 'price_minor', 'compare_at_price_minor', 'currency']);

        if ($variant && $promo) {
            $variant['compare_at_price_minor'] = $v->price_minor;
            $variant['price_minor'] = $promo->discount_type === 'percentage'
                ? (int) round($v->price_minor * (100 - $promo->discount_value) / 100)
                : max(0, $v->price_minor - $promo->discount_value);
        }

        return [
            'id' => $p->id,
            'slug' => $p->slug,
            'name' => $p->name,
            'image_url' => $p->getFirstMediaUrl('gallery') ?: null,
            'default_variant' => $variant,
        ];
    }
}