<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->wishlists()
            ->with('variant.product.media')
            ->latest()
            ->get()
            ->filter(fn ($w) => $w->variant?->product)
            ->map(fn ($w) => [
                'id' => $w->variant->product->id,
                'slug' => $w->variant->product->slug,
                'name' => $w->variant->product->name,
                'image_url' => $w->variant->product->getFirstMediaUrl('gallery') ?: null,
                'default_variant' => $w->variant->only(['id', 'price_minor', 'compare_at_price_minor', 'currency']),
            ])->values();

                    Seo::set([
            'title' => Seo::tr('Wishlist', 'المفضلة'),
            'noindex' => true,
        ]);
        return Inertia::render('Wishlist', ['items' => $items]);
    }

    // Heart button: saves the variant, or removes it if it is already saved.
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ]);

        $existing = $request->user()->wishlists()->where('product_variant_id', $data['product_variant_id'])->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['saved' => false]);
        }

        $request->user()->wishlists()->create(['product_variant_id' => $data['product_variant_id']]);

        return response()->json(['saved' => true]);
    }
}