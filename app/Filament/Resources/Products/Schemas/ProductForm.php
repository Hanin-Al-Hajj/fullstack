<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Content')
                ->schema([
                    Tabs::make('Language')
                        ->tabs([
                            Tab::make('English')
                                ->schema([
                                    TextInput::make('name.en')
                                        ->label('Name (English)')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (string $state, callable $set) {
                                            $set('slug', Str::slug($state));
                                        }),
                                    Textarea::make('short_description.en')
                                        ->label('Short description (English)')
                                        ->rows(2),
                                    RichEditor::make('description.en')
                                        ->label('Description (English)'),
                                ]),
                            Tab::make('Arabic')
                                ->schema([
                                    TextInput::make('name.ar')
                                        ->label('Name (Arabic)')
                                        ->required()
                                        ->extraInputAttributes(['dir' => 'rtl']),
                                    Textarea::make('short_description.ar')
                                        ->label('Short description (Arabic)')
                                        ->rows(2)
                                        ->extraInputAttributes(['dir' => 'rtl']),
                                    RichEditor::make('description.ar')
                                        ->label('Description (Arabic)')
                                        ->extraInputAttributes(['dir' => 'rtl']),
                                ]),
                        ]),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                ]),

            Section::make('Organization')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('category_id')
                            ->relationship('category', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name['en'] ?? $record->slug)
                            ->searchable()
                            ->required(),

                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->nullable(),
                    ]),
                ]),

            Section::make('Images')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->collection('gallery')
                        ->multiple()
                        ->reorderable()
                        ->image()
                        ->imageEditor(),
                ]),

            Section::make('Publishing')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        DateTimePicker::make('published_at')
                            ->label('Publish date'),
                    ]),
                ]),

        ]);
    }
}