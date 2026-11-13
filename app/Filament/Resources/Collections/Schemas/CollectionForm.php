<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.en')->label('Name (English)')->required(),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->extraInputAttributes(['dir' => 'rtl']),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('type')
                ->options([
                    'featured' => 'Featured (shown on the home page)',
                    'new_arrivals' => 'New arrivals',
                    'best_sellers' => 'Best sellers',
                    'manual' => 'Manual',
                ])
                ->default('manual')
                ->required(),
            Select::make('products')
                ->relationship('products', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name['en'] ?? $record->slug)
                ->multiple()
                ->searchable()
                ->preload(),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}