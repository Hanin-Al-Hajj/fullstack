<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Services\OrderStatusService;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order')->columns(4)->schema([
                TextEntry::make('order_number')->label('Order number')->weight('bold'),
                TextEntry::make('placed_at')->label('Placed')->dateTime(),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => OrderStatusService::label($state)),
                TextEntry::make('payment_status')->label('Payment')->badge()->formatStateUsing(fn (string $state) => ucfirst($state)),
            ]),

            Section::make('Customer and delivery')->columns(2)->schema([
                TextEntry::make('customer')->state(fn (Order $r) => $r->shipping_address['full_name'] ?? '-'),
                TextEntry::make('whatsapp')->label('WhatsApp / phone')->state(fn (Order $r) => $r->shipping_address['phone'] ?? '-'),
                TextEntry::make('email')->state(fn (Order $r) => $r->user?->email ?? $r->guest_email ?? '-'),
                TextEntry::make('account')->state(fn (Order $r) => $r->user_id ? 'Registered customer' : 'Guest'),
                TextEntry::make('address')->columnSpanFull()->state(function (Order $r) {
                    $a = $r->shipping_address;

                    return implode(' | ', array_filter([
                        implode(', ', array_filter([$a['street'] ?? null, ! empty($a['building']) ? 'Building '.$a['building'] : null, ! empty($a['floor']) ? 'Floor '.$a['floor'] : null])),
                        implode(', ', array_filter([$a['area'] ?? null, $a['city'] ?? null, $a['governorate'] ?? null])),
                        ! empty($a['landmark']) ? 'Near '.$a['landmark'] : null,
                    ]));
                }),
                TextEntry::make('notes')->label('Customer notes')->placeholder('-')->columnSpanFull(),
            ]),

            Section::make('Items')->schema([
                RepeatableEntry::make('items')->hiddenLabel()->columns(5)->schema([
                    TextEntry::make('product_name.en')->label('Product'),
                    TextEntry::make('variant_label')->label('Option')->placeholder('-'),
                    TextEntry::make('qty')->label('Qty'),
                    TextEntry::make('unit_price_minor')->label('Price')->money('USD', divideBy: 100),
                    TextEntry::make('line_total_minor')->label('Total')->money('USD', divideBy: 100),
                ]),
            ]),

            Section::make('Totals')->columns(5)->schema([
                TextEntry::make('subtotal_minor')->label('Subtotal')->money('USD', divideBy: 100),
                TextEntry::make('shipping_minor')->label('Delivery fee')->money('USD', divideBy: 100),
                TextEntry::make('discount_minor')->label('Discount')->money('USD', divideBy: 100),
                TextEntry::make('coupon.code')->label('Coupon')->placeholder('-'),
                TextEntry::make('total_minor')->label('Total to collect')->money('USD', divideBy: 100)->weight('bold'),
            ]),

            Section::make('Status history')->schema([
                RepeatableEntry::make('statusHistory')->hiddenLabel()->columns(4)->schema([
                    TextEntry::make('status')->badge()->formatStateUsing(fn (string $state) => OrderStatusService::label($state)),
                    TextEntry::make('created_at')->label('When')->dateTime(),
                    TextEntry::make('admin.name')->label('By')->placeholder('Customer / system'),
                    TextEntry::make('note')->placeholder('-'),
                ]),
            ]),
        ]);
    }
}