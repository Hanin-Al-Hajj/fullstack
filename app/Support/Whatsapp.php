<?php

namespace App\Support;

use App\Models\Order;

class Whatsapp
{
    // Turns what the customer typed into the digits WhatsApp needs (country code, no + or spaces).
    // "+961 70 123 456", "0096170123456", "070 123 456" and "70123456" all become 96170123456.
    public static function number(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '961')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '961'.ltrim($digits, '0');
        }

        return strlen($digits) <= 8 ? '961'.$digits : $digits;
    }

    // wa.me link that opens WhatsApp with a ready message; staff only press send.
    public static function confirmationUrl(Order $order): string
    {
        $a = $order->shipping_address;
        $name = $a['full_name'] ?? '';
        $store = config('store.name');
        $total = number_format($order->total_minor / 100, 2).' '.$order->currency;
        $min = config('store.delivery_days_min');
        $max = config('store.delivery_days_max');

        $text = "Hello {$name}, this is {$store}. We received your order {$order->order_number} (total {$total}, cash on delivery). "
            ."Delivery takes {$min} to {$max} days. Thank you!\n\n"
            ."مرحباً {$name}، معك {$store}. استلمنا طلبك رقم {$order->order_number} (المجموع {$total} نقداً عند الاستلام). "
            ."مدة التوصيل من {$min} إلى {$max} أيام. شكراً لك!";

        return 'https://wa.me/'.self::number($a['phone'] ?? '').'?text='.rawurlencode($text);
    }
}