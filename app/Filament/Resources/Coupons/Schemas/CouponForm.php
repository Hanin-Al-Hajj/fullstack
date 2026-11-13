<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->maxLength(40)
                ->unique(ignoreRecord: true)
                ->dehydrateStateUsing(fn (string $state) => strtoupper(trim($state)))
                ->helperText('What the customer types at checkout, e.g. WELCOME10'),

            Select::make('type')
                ->options(['percentage' => 'Percentage off', 'fixed' => 'Fixed amount off'])
                ->default('percentage')
                ->required()
                ->live(),

            TextInput::make('value')
                ->label('Discount')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(fn (callable $get) => $get('type') === 'percentage' ? 100 : null)
                ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : 'cents (500 = $5.00)'),

            TextInput::make('min_spend_minor')
                ->label('Minimum order (optional)')
                ->numeric()
                ->minValue(0)
                ->prefix('$')
                ->formatStateUsing(fn ($state) => $state === null ? null : $state / 100)
                ->dehydrateStateUsing(fn ($state) => ($state === null || $state === '') ? null : (int) round($state * 100)),

            TextInput::make('max_uses')->label('Total uses allowed (optional)')->numeric()->minValue(1),

            TextInput::make('used_count')->label('Times used')->numeric()->disabled()->dehydrated(false),

            DateTimePicker::make('starts_at')->label('Valid from (optional)'),
            DateTimePicker::make('expires_at')->label('Valid until (optional)'),

            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}