<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title.en')->label('Title (English)'),
            TextInput::make('title.ar')->label('Title (Arabic)')->extraInputAttributes(['dir' => 'rtl']),
            SpatieMediaLibraryFileUpload::make('image')->collection('banner')->image(),
            TextInput::make('link_url')->label('Link (optional, e.g. /shop)'),
            TextInput::make('sort_order')->numeric()->default(0),
            DateTimePicker::make('starts_at')->label('Show from (optional)'),
            DateTimePicker::make('ends_at')->label('Show until (optional)'),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}