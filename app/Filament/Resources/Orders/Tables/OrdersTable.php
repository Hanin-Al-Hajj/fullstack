<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Services\OrderStatusService;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->searchable()->weight('bold'),
                TextColumn::make('shipping_address.full_name')
                    ->label('Customer')
                    ->searchable(query: fn ($query, string $search) => $query->where('shipping_address->full_name', 'like', "%{$search}%")),
                TextColumn::make('shipping_address.phone')
                    ->label('WhatsApp')
                    ->searchable(query: fn ($query, string $search) => $query->where('shipping_address->phone', 'like', "%{$search}%")),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed', 'processing' => 'info',
                        'shipped', 'out_for_delivery' => 'primary',
                        'delivered' => 'success',
                        'cancelled', 'delivery_failed', 'returned' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => OrderStatusService::label($state)),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('total_minor')->label('Total')->money('USD', divideBy: 100)->sortable(),
                TextColumn::make('placed_at')->label('Placed')->dateTime('M j, H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatusService::options()),
                SelectFilter::make('payment_status')->label('Payment')->options([
                    'unpaid' => 'Unpaid',
                    'paid' => 'Paid',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}