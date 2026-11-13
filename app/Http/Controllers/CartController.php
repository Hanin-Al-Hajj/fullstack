<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->current()->load([
            'items.variant.attributeValues',
            'items.variant.product.media',
        ]);

        $items = $cart->items
            ->filter(fn (CartItem $i) => $i->variant?->product)
            ->map(function (CartItem $item) {
                $v = $item->variant;
                $max = $v->backorder_allowed ? 99 : $v->stock_qty;

                return [
                    'id' => $item->id,
                    'qty' => $item->qty,
                    'unit_price_minor' => app(\App\Services\PricingService::class)->unitPriceMinor($v),
                    'currency' => $v->currency,
                    'max_qty' => $max,
                    'problem' => ! $v->is_active || $item->qty > $max,
                    'sku' => $v->sku,
                    'options' => $v->attributeValues->pluck('value')->values(),
                    'product' => [
                        'id' => $v->product->id,
                        'slug' => $v->product->slug,
                        'name' => $v->product->name,
                        'image_url' => $v->product->getFirstMediaUrl('gallery', 'thumb') ?: null,
                    ],
                ];
            })->values();

                    Seo::set([
            'title' => Seo::tr('Cart', 'السلة'),
            'noindex' => true,
        ]);
        
        return Inertia::render('Cart', [
            'items' => $items,
            'subtotal_minor' => $this->cartService->subtotalMinor(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($data['product_variant_id']);

        if (! $variant->is_active || ! $variant->inStock()) {
            return response()->json(['message' => __('store.item_unavailable')], 422);
        }

        $item = $this->cartService->addItem($variant, $data['qty']);

        return response()->json([
            'item' => $item->load('variant.product'),
            'subtotal_minor' => $this->cartService->subtotalMinor(),
        ], 201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwnership($cartItem);

        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateQty($cartItem, $data['qty']);

        return response()->json([
            'subtotal_minor' => $this->cartService->subtotalMinor(),
        ]);
    }

    public function destroy(CartItem $cartItem)
    {
        $this->authorizeOwnership($cartItem);

        $this->cartService->removeItem($cartItem);

        return response()->json([
            'subtotal_minor' => $this->cartService->subtotalMinor(),
        ]);
    }

    /**
     * Without this, anyone could PATCH/DELETE any cart_item id and
     * tamper with a different customer's cart just by guessing numbers.
     */
    private function authorizeOwnership(CartItem $cartItem): void
    {
        abort_unless(
            $cartItem->cart_id === $this->cartService->current()->id,
            403,
            __('store.cart_item_not_yours')
        );
    }
}
