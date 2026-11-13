<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Zone name')->required(),

            CheckboxList::make('governorates')
                ->label('Governorates this fee applies to')
                ->options([
                    'Beirut' => 'Beirut',
                    'Mount Lebanon' => 'Mount Lebanon',
                    'North Lebanon' => 'North Lebanon',
                    'Akkar' => 'Akkar',
                    'Beqaa' => 'Beqaa',
                    'Baalbek-Hermel' => 'Baalbek-Hermel',
                    'South Lebanon' => 'South Lebanon',
                    'Nabatieh' => 'Nabatieh',
                ])
                ->columns(2)
                ->bulkToggleable()
                ->required(),

            Toggle::make('is_active')->label('Active')->default(true),

            // One flat delivery fee per zone: the store shows this amount at checkout.
            Repeater::make('rates')
                ->relationship()
                ->label('Delivery fee')
                ->minItems(1)
                ->maxItems(1)
                ->defaultItems(1)
                ->addable(false)
                ->deletable(false)
                ->schema([
                    Hidden::make('name')->default('Delivery'),
                    TextInput::make('rate_minor')
                        ->label('Fee')
                        ->prefix('$')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->formatStateUsing(fn ($state) => $state === null ? null : $state / 100)
                        ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                ])
                ->columnSpanFull(),
        ]);
    }
}