<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// Private notes about a customer. Customers never see them.
class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Private notes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('note')->required()->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('note')->wrap(),
                TextColumn::make('admin.name')->label('By'),
                TextColumn::make('created_at')->label('When')->dateTime('M j, H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add note')
                    ->mutateDataUsing(fn (array $data) => [...$data, 'admin_id' => Filament::auth()->id()]),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
