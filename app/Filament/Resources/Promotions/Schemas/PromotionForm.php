<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.en')->label('Name (English)')->required(),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->extraInputAttributes(['dir' => 'rtl']),
            Select::make('discount_type')
                ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed amount'])
                ->default('percentage')
                ->required(),
            TextInput::make('discount_value')
                ->label('Discount (percent, or minor units like cents if fixed)')
                ->numeric()
                ->required(),
            DateTimePicker::make('starts_at')->required(),
            DateTimePicker::make('ends_at')->required(),
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