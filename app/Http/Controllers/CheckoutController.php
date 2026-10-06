<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
    ) {}

    /**
     * Everything the checkout page needs: the current cart, the user's
     * saved addresses if logged in, and available shipping options.
     * Returns JSON for now — swap this to Inertia::render() once the
     * React checkout page exists; the data shape stays the same either way.
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->current()->load('items.variant.product');

        return response()->json([
            'cart' => $cart,
            'subtotal_minor' => $this->cartService->subtotalMinor(),
            'addresses' => $request->user()?->addresses ?? [],
            'shipping_zones' => ShippingZone::with('rates')->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_address' => ['required', 'array'],
            'shipping_address.full_name' => ['required', 'string'],
            'shipping_address.phone' => ['required', 'string'],
            'shipping_address.governorate' => ['required', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.street' => ['required', 'string'],
            'shipping_rate_id' => ['required', 'integer', 'exists:shipping_rates,id'],
            'coupon_code' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'in:cod'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $request->user() && empty($data['guest_email'])) {
            return response()->json([
                'message' => 'An email address is required for guest checkout.',
            ], 422);
        }

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $cart = $this->cartService->current();

        try {
            $order = $this->checkoutService->placeOrder($cart, $data);
        } catch (CheckoutException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'order' => $order->load('items'),
            'message' => 'Order placed successfully.',
        ], 201);
    }
}