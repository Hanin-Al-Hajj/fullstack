<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->current()->load('items.variant.product');

        return response()->json([
            'cart' => $cart,
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
            return response()->json(['message' => 'This item is currently unavailable.'], 422);
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
            'This cart item does not belong to your cart.'
        );
    }
}