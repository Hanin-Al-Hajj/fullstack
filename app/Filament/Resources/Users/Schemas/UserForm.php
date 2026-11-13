<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('phone')->label('WhatsApp / phone')->tel()->maxLength(30),
            Select::make('locale')->label('Language')->options(['en' => 'English', 'ar' => 'العربية'])->required(),
        ]);
    }
}
