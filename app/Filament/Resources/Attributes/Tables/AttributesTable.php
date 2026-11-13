<?php

namespace App\Filament\Resources\Attributes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name.en')
                    ->label('Name')
                    ->searchable(query: fn ($query, string $search) => $query->where('name->en', 'like', "%{$search}%")),
                TextColumn::make('name.ar')->label('Arabic'),
                TextColumn::make('type'),
                TextColumn::make('values_count')->counts('values')->label('Values'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}