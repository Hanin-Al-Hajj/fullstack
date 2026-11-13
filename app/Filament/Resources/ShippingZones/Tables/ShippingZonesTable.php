<?php

namespace App\Filament\Resources\ShippingZones\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('rates'))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('governorates')->badge(),
                TextColumn::make('fee')
                    ->label('Delivery fee')
                    ->state(fn ($record) => $record->rates->first()?->rate_minor)
                    ->money('USD', divideBy: 100),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}