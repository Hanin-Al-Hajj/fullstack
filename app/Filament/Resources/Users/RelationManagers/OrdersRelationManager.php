<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderStatusService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->weight('bold'),
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
                TextColumn::make('total_minor')->label('Total')->money('USD', divideBy: 100),
                TextColumn::make('placed_at')->label('Placed')->dateTime('M j, H:i'),
            ])
            ->recordUrl(fn ($record) => OrderResource::getUrl('view', ['record' => $record]));
    }
}
