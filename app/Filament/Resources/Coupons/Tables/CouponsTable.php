<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->weight('bold')->copyable(),
                TextColumn::make('discount')
                    ->state(fn ($record) => $record->type === 'percentage'
                        ? "{$record->value}%"
                        : '$'.number_format($record->value / 100, 2)),
                TextColumn::make('min_spend_minor')->label('Minimum order')->money('USD', divideBy: 100)->placeholder('None'),
                TextColumn::make('used')
                    ->state(fn ($record) => $record->used_count.' / '.($record->max_uses ?? 'unlimited')),
                TextColumn::make('expires_at')->label('Valid until')->dateTime('M j, Y')->placeholder('No expiry'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}