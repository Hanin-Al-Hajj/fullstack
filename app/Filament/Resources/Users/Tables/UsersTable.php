<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount('orders')
                ->withSum(['orders as delivered_total' => fn ($o) => $o->where('status', 'delivered')], 'total_minor'))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('WhatsApp')->searchable()->placeholder('-'),
                TextColumn::make('orders_count')->label('Orders')->sortable(),
                TextColumn::make('delivered_total')->label('Spent (delivered)')->money('USD', divideBy: 100)->sortable(),
                TextColumn::make('created_at')->label('Registered')->date('M j, Y')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
