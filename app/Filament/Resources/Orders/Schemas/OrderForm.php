<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('guest_email')
                    ->email(),
                TextInput::make('guest_phone')
                    ->tel(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'delivery_failed' => 'Delivery failed',
            'returned' => 'Returned',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                    ->default('unpaid')
                    ->required(),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('exchange_rate')
                    ->numeric(),
                TextInput::make('subtotal_minor')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_minor')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_minor')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_minor')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_minor')
                    ->required()
                    ->numeric(),
                TextInput::make('coupon_id')
                    ->numeric(),
                TextInput::make('shipping_rate_id')
                    ->numeric(),
                TextInput::make('shipping_method_label'),
                TextInput::make('shipping_address')
                    ->required(),
                TextInput::make('billing_address'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('placed_at'),
            ]);
    }
}
