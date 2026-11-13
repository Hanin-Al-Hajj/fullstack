<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class CartService
{
    private const SESSION_KEY = 'cart_token';

    /**
     * Get the current cart, creating one if none exists yet.
     * Logged-in users get a DB-linked cart by user_id; guests get one
     * keyed by a session token so it survives across page loads.
     */
    public function current(): Cart
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(
                ['user_id' => auth()->id()],
                ['currency' => 'USD']
            );
        }

        $token = session(self::SESSION_KEY);

        if ($token) {
            $cart = Cart::where('session_token', $token)->first();
            if ($cart) {
                return $cart;
            }
        }

        $token = (string) Str::uuid();
        session([self::SESSION_KEY => $token]);

        return Cart::create([
            'session_token' => $token,
            'currency' => 'USD',
        ]);
    }

    public function addItem(ProductVariant $variant, int $qty = 1): CartItem
    {
        $cart = $this->current();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($item) {
            $item->qty += $qty;
            $item->save();

            return $item;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'qty' => $qty,
            'unit_price_minor' => $variant->price_minor,
        ]);
    }

    public function updateQty(CartItem $item, int $qty): void
    {
        if ($qty <= 0) {
            $item->delete();

            return;
        }

        $item->update(['qty' => $qty]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * The guest cart's session token, if one exists — used by the login
     * listener to find and merge a guest's cart into their account.
     */
    public function currentSessionToken(): ?string
    {
        return session(self::SESSION_KEY);
    }

    public function forgetSessionToken(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    // Like itemCount() but never creates a cart (used for the header badge on every page).
    public function peekCount(): int
    {
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
        } else {
            $token = session(self::SESSION_KEY);
            $cart = $token ? Cart::where('session_token', $token)->first() : null;
        }

        return $cart ? (int) $cart->items()->sum('qty') : 0;
    }

    public function itemCount(): int
    {
        return $this->current()->items()->sum('qty');
    }

    public function subtotalMinor(): int
    {
        $pricing = app(PricingService::class);

        return $this->current()->items()->with('variant')->get()->sum(
            fn (CartItem $item) => $pricing->unitPriceMinor($item->variant) * $item->qty
        );
    }

    /**
     * Call this right after a guest logs in, before the session cart
     * token is lost, to fold their guest cart into their account cart.
     */
    public function mergeGuestCartIntoUser(string $guestToken, int $userId): void
    {
        $guestCart = Cart::where('session_token', $guestToken)->first();
        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId], ['currency' => 'USD']);

        foreach ($guestCart->items as $guestItem) {
            $existing = CartItem::where('cart_id', $userCart->id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('qty', $guestItem->qty);
                $guestItem->delete();
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }
}