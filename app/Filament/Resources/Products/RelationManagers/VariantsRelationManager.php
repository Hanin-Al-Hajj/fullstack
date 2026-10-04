<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('price_minor')
                    ->label('Price (minor units, e.g. cents)')
                    ->numeric()
                    ->required(),

                TextInput::make('compare_at_price_minor')
                    ->label('Compare-at price (optional)')
                    ->numeric(),

                TextInput::make('stock_qty')
                    ->label('Stock quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]),

            Grid::make(3)->schema([
                Toggle::make('is_default')->label('Default variant'),
                Toggle::make('is_active')->label('Active')->default(true),
                Toggle::make('backorder_allowed')->label('Allow backorder'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')->searchable(),
                TextColumn::make('price_minor')->label('Price')->money(divideBy: 100),
                TextColumn::make('stock_qty')->label('Stock'),
                IconColumn::make('is_default')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}