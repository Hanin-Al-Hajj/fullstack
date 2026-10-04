<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Overview')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name.en')
                            ->label('Name (English)'),
                        TextEntry::make('name.ar')
                            ->label('Name (Arabic)'),
                    ]),

                    TextEntry::make('slug'),

                    Grid::make(2)->schema([
                        TextEntry::make('category')
                            ->label('Category')
                            ->getStateUsing(fn ($record) => $record->category?->name['en'] ?? $record->category?->slug ?? '—'),
                        TextEntry::make('brand.name')
                            ->label('Brand')
                            ->placeholder('—'),
                    ]),

                    Grid::make(2)->schema([
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('published_at')
                            ->label('Published')
                            ->dateTime()
                            ->placeholder('Not published'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
                ]),
        ]);
    }
}