<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class OrderController extends Controller
{
    // "My orders": the signed-in customer's past orders, newest first.
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest('placed_at')
            ->paginate(10)
            ->through(fn (Order $o) => [
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_label' => OrderStatusService::label($o->status),
                'total_minor' => $o->total_minor,
                'currency' => $o->currency,
                'placed_at' => $o->placed_at?->toIso8601String(),
                'items_count' => $o->items_count,
            ]);

                    Seo::set([
            'title' => Seo::tr('My orders', 'طلباتي'),
            'noindex' => true,
        ]);

        return Inertia::render('Orders', ['orders' => $orders]);
    }

    /**
     * One order. Order numbers are guessable enough that the page must not be
     * public: only the logged-in owner, or the browser session that just
     * placed the order, can open it.
     */
    public function confirmation(Order $order)
    {
        $owns = auth()->check() && $order->user_id === auth()->id();
        $justPlaced = in_array($order->order_number, session('placed_orders', []), true);

        abort_unless($owns || $justPlaced, 404);

        $order->load('items');

                Seo::set([
            'title' => Seo::tr('Order', 'الطلب').' '.$order->order_number,
            'noindex' => true,
        ]);

        return Inertia::render('OrderConfirmation', [
            'order' => [
                ...$order->only([
                    'order_number', 'status', 'currency', 'payment_method', 'shipping_method_label',
                    'subtotal_minor', 'shipping_minor', 'discount_minor', 'total_minor',
                    'shipping_address',
                ]),
                'status_label' => OrderStatusService::label($order->status),
                'placed_at' => $order->placed_at?->toIso8601String(),
                'is_new' => $justPlaced,
                'can_reorder' => $owns,
                'items' => $order->items->map(fn ($i) => $i->only([
                    'product_name', 'variant_label', 'sku', 'qty', 'unit_price_minor', 'line_total_minor',
                ]))->values(),
            ],
        ]);
    }

    // "Order again": puts the items that are still available into the cart.
    public function reorder(Order $order, CartService $cart)
    {
        abort_unless(auth()->check() && $order->user_id === auth()->id(), 404);

        $added = 0;
        $skipped = 0;

        foreach ($order->items()->with('variant.product')->get() as $item) {
            $variant = $item->variant;

            if (! $variant || ! $variant->product || ! $variant->is_active || ! $variant->inStock()) {
                $skipped++;

                continue;
            }

            $qty = $variant->backorder_allowed ? $item->qty : min($item->qty, $variant->stock_qty);
            $cart->addItem($variant, max(1, $qty));
            $added++;
        }

        return response()->json(['added' => $added, 'skipped' => $skipped]);
    }
}
