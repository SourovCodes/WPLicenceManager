<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        Select::make('type')
                            ->options([
                                'plugin' => 'Plugin',
                                'theme' => 'Theme',
                            ])
                            ->default('plugin')
                            ->required(),
                        TextInput::make('version')
                            ->default('1.0.0')
                            ->required()
                            ->maxLength(50),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        TextInput::make('download_url')
                            ->label('Download URL')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Settings')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable this product'),
                        Toggle::make('requires_license')
                            ->label('Requires License')
                            ->default(true)
                            ->helperText('Require license for updates'),
                        Toggle::make('has_api_access')
                            ->label('Has API Access')
                            ->default(false)
                            ->helperText('Allow premium API access'),
                    ]),
            ]);
    }
}
