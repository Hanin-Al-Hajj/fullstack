<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('_lft')
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('default')->circular(),
                TextColumn::make('name.en')
                    ->label('Name')
                    ->searchable(query: fn ($query, string $search) => $query->where('name->en', 'like', "%{$search}%")),
                TextColumn::make('name.ar')->label('Arabic'),
                TextColumn::make('parent')
                    ->label('Parent')
                    ->state(fn ($record) => $record->parent?->name['en'] ?? null)
                    ->placeholder('Top level'),
                TextColumn::make('products_count')->counts('products')->label('Products'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}