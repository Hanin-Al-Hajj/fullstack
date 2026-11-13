<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderStatusService
{
    public const LABELS = [
        'pending' => 'New',
        'confirmed' => 'Confirmed',
        'processing' => 'Preparing',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'delivery_failed' => 'Delivery failed',
        'returned' => 'Returned',
        'cancelled' => 'Cancelled',
    ];

    // Which statuses an order may move to from each status.
    private const NEXT = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['out_for_delivery', 'delivery_failed', 'cancelled'],
        'out_for_delivery' => ['delivered', 'delivery_failed'],
        'delivery_failed' => ['out_for_delivery', 'returned', 'cancelled'],
        'delivered' => ['returned'],
        'returned' => [],
        'cancelled' => [],
    ];

    public static function label(?string $status): string
    {
        return self::LABELS[$status] ?? (string) $status;
    }

    public static function options(): array
    {
        return self::LABELS;
    }

    public static function next(string $status): array
    {
        return self::NEXT[$status] ?? [];
    }

    /**
     * Moves an order to a new status, keeps a history entry, and keeps stock and
     * payment in step: cancelled/returned orders put their items back in stock,
     * and a delivered cash order counts as paid.
     */
    public function change(Order $order, string $to, ?string $note = null, ?int $adminId = null): void
    {
        if (! in_array($to, self::next($order->status), true)) {
            throw new InvalidArgumentException('This order cannot move from "'.self::label($order->status).'" to "'.self::label($to).'".');
        }

        DB::transaction(function () use ($order, $to, $note, $adminId) {
            if (in_array($to, ['cancelled', 'returned'], true)) {
                foreach ($order->items as $item) {
                    $variant = $item->variant()->lockForUpdate()->first();

                    // Checkout only took stock for variants without backorder.
                    if ($variant && ! $variant->backorder_allowed) {
                        $variant->increment('stock_qty', $item->qty);
                    }
                }
            }

            $changes = ['status' => $to];

            if ($to === 'delivered') {
                $changes['payment_status'] = 'paid';
                $order->payments()->create([
                    'gateway' => 'cod',
                    'amount_minor' => $order->total_minor,
                    'status' => 'succeeded',
                ]);
            }

            if ($to === 'returned' && $order->payment_status === 'paid') {
                $changes['payment_status'] = 'refunded';
            }

            $order->update($changes);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $to,
                'note' => $note,
                'changed_by' => $adminId,
            ]);
        });
    }
}