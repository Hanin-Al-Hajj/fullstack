<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Whatsapp;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')->columns(3)->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('phone')
                    ->label('WhatsApp / phone')
                    ->placeholder('-')
                    ->url(fn (User $r) => $r->phone ? 'https://wa.me/'.Whatsapp::number($r->phone) : null)
                    ->openUrlInNewTab(),
                TextEntry::make('locale')->label('Language')->formatStateUsing(fn ($state) => $state === 'ar' ? 'العربية' : 'English'),
                TextEntry::make('created_at')->label('Registered')->dateTime(),
                TextEntry::make('orders_total')->label('Orders')->state(fn (User $r) => $r->orders()->count()),
            ]),

            Section::make('Saved addresses')->schema([
                RepeatableEntry::make('addresses')->hiddenLabel()->columns(5)->schema([
                    TextEntry::make('label')->placeholder('-'),
                    TextEntry::make('full_name')->label('Name'),
                    TextEntry::make('phone'),
                    TextEntry::make('city'),
                    TextEntry::make('street'),
                ]),
            ]),
        ]);
    }
}
