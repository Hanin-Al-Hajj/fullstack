<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name.en')
                ->label('Name (English)')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug($state ?? ''))),
            TextInput::make('name.ar')->label('Name (Arabic)')->required()->extraInputAttributes(['dir' => 'rtl']),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),

            // A category cannot be placed under itself or under one of its own subcategories.
            Select::make('parent_id')
                ->label('Parent category')
                ->placeholder('None (top level)')
                ->searchable()
                ->options(function ($record) {
                    $blocked = $record ? $record->descendants()->pluck('id')->push($record->id) : collect();

                    return Category::withDepth()->orderBy('_lft')->get()
                        ->reject(fn ($c) => $blocked->contains($c->id))
                        ->mapWithKeys(fn ($c) => [$c->id => str_repeat('— ', $c->depth).($c->name['en'] ?? $c->slug)]);
                }),

            SpatieMediaLibraryFileUpload::make('image')->collection('default')->image()->label('Image'),

            Textarea::make('description.en')->label('Description (English)')->rows(2),
            Textarea::make('description.ar')->label('Description (Arabic)')->rows(2)->extraInputAttributes(['dir' => 'rtl']),

            TextInput::make('sort_order')->numeric()->default(0)->helperText('Smaller numbers show first.'),
            Toggle::make('is_active')->label('Active')->default(true),

            Section::make('Search engines (optional)')->collapsed()->schema([
                TextInput::make('seo_title.en')->label('Title (English)'),
                TextInput::make('seo_title.ar')->label('Title (Arabic)')->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('seo_description.en')->label('Description (English)')->rows(2),
                Textarea::make('seo_description.ar')->label('Description (Arabic)')->rows(2)->extraInputAttributes(['dir' => 'rtl']),
            ]),
        ]);
    }
}