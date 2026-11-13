<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\CartItem;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
        private PricingService $pricing,
    ) {}

    public function show(Request $request)
    {
        $cart = $this->cartService->current()->load([
            'items.variant.attributeValues',
            'items.variant.product.media',
        ]);

        if ($cart->items->isEmpty()) {
            return redirect('/cart');
        }

        $items = $cart->items
            ->filter(fn (CartItem $i) => $i->variant?->product)
            ->map(fn (CartItem $i) => [
                'id' => $i->id,
                'qty' => $i->qty,
                'unit_price_minor' => $this->pricing->unitPriceMinor($i->variant),
                'currency' => $i->variant->currency,
                'sku' => $i->variant->sku,
                'options' => $i->variant->attributeValues->pluck('value')->values(),
                'product' => [
                    'id' => $i->variant->product->id,
                    'name' => $i->variant->product->name,
                    'image_url' => $i->variant->product->getFirstMediaUrl('gallery', 'thumb') ?: null,
                ],
            ])->values();

                    Seo::set([
            'title' => Seo::tr('Checkout', 'إتمام الطلب'),
            'noindex' => true,
        ]);

        return Inertia::render('Checkout', [
            'items' => $items,
            'subtotal_minor' => $this->cartService->subtotalMinor(),
            'zones' => ShippingZone::with('rates')->where('is_active', true)->get()->map(fn ($z) => [
                'id' => $z->id,
                'governorates' => $z->governorates,
                'rates' => $z->rates->map(fn ($r) => $r->only(['id', 'name', 'rate_minor', 'free_shipping_over_minor']))->values(),
            ])->values(),
            'customer' => $request->user()?->only(['name', 'email', 'phone']),
            'addresses' => $request->user()?->addresses()->orderByDesc('is_default')->get() ?? [],
        ]);
    }

    // Totals preview (shipping + coupon). Writes nothing.
    public function quote(Request $request)
    {
        $data = $request->validate([
            'shipping_rate_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string'],
        ]);

        try {
            return response()->json($this->checkoutService->quote(
                $this->cartService->current(),
                $data['shipping_rate_id'] ?? null,
                $data['coupon_code'] ?? null,
            ));
        } catch (CheckoutException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
            'shipping_address.area' => ['nullable', 'string'],
            'shipping_address.building' => ['nullable', 'string'],
            'shipping_address.floor' => ['nullable', 'string'],
            'shipping_address.landmark' => ['nullable', 'string'],
            'shipping_rate_id' => ['required', 'integer', 'exists:shipping_rates,id'],
            'coupon_code' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'in:cod'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $request->user() && empty($data['guest_email'])) {
            return response()->json(['message' => __('store.guest_email_required')], 422);
        }

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        try {
            $order = $this->checkoutService->placeOrder($this->cartService->current(), $data);
        } catch (CheckoutException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Lets this browser session (guest or not) open the confirmation page.
        $request->session()->push('placed_orders', $order->order_number);

        return response()->json(['order' => $order->load('items'), 'message' => __('store.order_placed')], 201);
    }
}
