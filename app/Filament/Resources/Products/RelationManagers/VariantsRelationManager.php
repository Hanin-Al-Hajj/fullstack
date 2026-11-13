<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\AttributeValue;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
            Select::make('attributeValues')
                ->label('Options (color, size...)')
                ->relationship(
                    name: 'attributeValues',
                    titleAttribute: 'id',
                    modifyQueryUsing: fn ($query) => $query->with('attribute'),
                )
                ->getOptionLabelFromRecordUsing(
                    fn ($record) => ($record->attribute->name['en'] ?? '').': '.($record->value['en'] ?? '')
                )
                ->multiple()
                ->preload()
                ->rules([
                    fn () => function (string $attribute, $value, Closure $fail) {
                        $attributeIds = AttributeValue::whereIn('id', (array) $value)->pluck('attribute_id');

                        if ($attributeIds->count() !== $attributeIds->unique()->count()) {
                            $fail('Pick only one value for each option (one color, one size...).');
                        }
                    },
                ])
                ->columnSpanFull(),

            Grid::make(2)->schema([
                TextInput::make('sku')->required()->unique(ignoreRecord: true),

                TextInput::make('price_minor')
                    ->label('Price (minor units, e.g. cents)')
                    ->numeric()
                    ->required(),

                TextInput::make('compare_at_price_minor')
                    ->label('Old price, shown struck through (optional)')
                    ->numeric(),

                TextInput::make('stock_qty')
                    ->label('Stock quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),

                TextInput::make('low_stock_threshold')
                    ->label('Low stock warning at')
                    ->numeric()
                    ->default(5),
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
            ->modifyQueryUsing(fn ($query) => $query->with('attributeValues'))
            ->columns([
                TextColumn::make('sku')->searchable(),
                TextColumn::make('options')
                    ->state(fn ($record) => $record->attributeValues->map(fn ($v) => $v->value['en'] ?? '')->implode(' / '))
                    ->placeholder('-'),
                TextColumn::make('price_minor')->label('Price')->money('USD', divideBy: 100),
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