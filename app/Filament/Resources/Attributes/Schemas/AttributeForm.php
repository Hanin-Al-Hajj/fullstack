<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.en')
                ->label('Name (English), e.g. Color, Size, Storage')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->extraInputAttributes(['dir' => 'rtl']),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('type')
                ->options(['select' => 'List of values (size, storage...)', 'color' => 'Color'])
                ->default('select')
                ->required(),

            Repeater::make('values')
                ->relationship()
                ->label('Values')
                ->schema([
                    TextInput::make('value.en')->label('English (Red, M, 128GB...)')->required(),
                    TextInput::make('value.ar')->label('Arabic')->required()->extraInputAttributes(['dir' => 'rtl']),
                    ColorPicker::make('meta.hex')->label('Swatch (colors only)'),
                ])
                ->columns(3)
                ->addActionLabel('Add value')
                ->reorderable(false)
                ->columnSpanFull(),
        ]);
    }
}