<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function shippingRatesFor(string $governorate)
    {
        return ShippingRate::whereHas('zone', function ($q) use ($governorate) {
            $q->where('is_active', true)
                ->whereJsonContains('governorates', $governorate);
        })->with('zone')->get();
    }

    public function validateCoupon(string $code, int $subtotalMinor): Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($subtotalMinor)) {
            throw new CheckoutException('This coupon code is invalid, expired, or does not apply to this order.');
        }

        return $coupon;
    }

    /**
     * Turns a cart into a real order: validates stock, locks the rows
     * involved so two simultaneous checkouts can't oversell the last
     * unit, computes totals, and writes everything in one transaction.
     *
     * @param  array{
     *   user_id?: int|null,
     *   guest_email?: string|null,
     *   guest_phone?: string|null,
     *   shipping_address: array,
     *   billing_address?: array|null,
     *   shipping_rate_id: int,
     *   coupon_code?: string|null,
     *   payment_method?: string,
     *   notes?: string|null,
     * }  $data
     */
    public function placeOrder(Cart $cart, array $data): Order
    {
        $cart->load('items.variant.product');

        if ($cart->items->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        return DB::transaction(function () use ($cart, $data) {
            $lockedVariants = $this->lockAndValidateStock($cart);

            $subtotalMinor = 0;
            foreach ($cart->items as $item) {
                $subtotalMinor += $lockedVariants[$item->id]->price_minor * $item->qty;
            }

            [$shippingRate, $shippingMinor] = $this->resolveShipping($data, $subtotalMinor);

            $coupon = null;
            $discountMinor = 0;
            if (! empty($data['coupon_code'])) {
                $coupon = $this->validateCoupon($data['coupon_code'], $subtotalMinor);
                $discountMinor = $coupon->type === 'percentage'
                    ? (int) round($subtotalMinor * $coupon->value / 100)
                    : min($coupon->value, $subtotalMinor);
            }

            $taxMinor = 0; // No tax engine yet — hook in here once tax rules are defined in Settings.

            $totalMinor = $subtotalMinor - $discountMinor + $shippingMinor + $taxMinor;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $data['user_id'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? 'cod',
                'currency' => 'USD',
                'subtotal_minor' => $subtotalMinor,
                'shipping_minor' => $shippingMinor,
                'discount_minor' => $discountMinor,
                'tax_minor' => $taxMinor,
                'total_minor' => $totalMinor,
                'coupon_id' => $coupon?->id,
                'shipping_rate_id' => $shippingRate->id,
                'shipping_method_label' => $shippingRate->name,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            $this->createOrderItemsAndDecrementStock($order, $cart, $lockedVariants);

            $coupon?->increment('used_count');

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'note' => 'Order placed.',
            ]);

            $cart->items()->delete();

            return $order;
        });
    }

    private function lockAndValidateStock(Cart $cart): array
    {
        $locked = [];

        foreach ($cart->items as $item) {
            // lockForUpdate holds a row lock until the transaction commits,
            // so a second checkout racing for the same last unit has to
            // wait, rather than both succeeding and overselling.
            $variant = $item->variant()->lockForUpdate()->first();

            if (! $variant) {
                throw new CheckoutException('An item in your cart is no longer available.');
            }

            if (! $variant->backorder_allowed && $variant->stock_qty < $item->qty) {
                throw new CheckoutException(
                    "'{$variant->sku}' doesn't have enough stock ({$variant->stock_qty} left). Please update your cart."
                );
            }

            $locked[$item->id] = $variant;
        }

        return $locked;
    }

    private function resolveShipping(array $data, int $subtotalMinor): array
    {
        $shippingRate = ShippingRate::with('zone')->find($data['shipping_rate_id'] ?? null);

        if (! $shippingRate) {
            throw new CheckoutException('Please choose a valid shipping method.');
        }

        $shippingMinor = $shippingRate->rate_minor;

        if ($shippingRate->free_shipping_over_minor && $subtotalMinor >= $shippingRate->free_shipping_over_minor) {
            $shippingMinor = 0;
        }

        return [$shippingRate, $shippingMinor];
    }

    private function createOrderItemsAndDecrementStock(Order $order, Cart $cart, array $lockedVariants): void
    {
        foreach ($cart->items as $item) {
            $variant = $lockedVariants[$item->id];

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name, // snapshot — survives later product edits/deletes
                'variant_label' => $variant->attributeValues
                    ->pluck('value')
                    ->map(fn ($v) => $v[app()->getLocale()] ?? $v['en'] ?? '')
                    ->implode(' / '),
                'sku' => $variant->sku,
                'unit_price_minor' => $variant->price_minor,
                'qty' => $item->qty,
                'line_total_minor' => $variant->price_minor * $item->qty,
            ]);

            if (! $variant->backorder_allowed) {
                $variant->decrement('stock_qty', $item->qty);
            }
        }
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}