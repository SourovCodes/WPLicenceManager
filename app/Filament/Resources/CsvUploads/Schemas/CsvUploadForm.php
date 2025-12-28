<?php

namespace App\Filament\Resources\CsvUploads\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CsvUploadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('License')
                    ->schema([
                        Select::make('license_id')
                            ->relationship('license', 'license_key')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('SFTP Configuration')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sftp_host')
                            ->label('Host')
                            ->required()
                            ->placeholder('sftp.example.com'),
                        TextInput::make('sftp_port')
                            ->label('Port')
                            ->required()
                            ->numeric()
                            ->default(22),
                        TextInput::make('sftp_username')
                            ->label('Username')
                            ->required(),
                        TextInput::make('sftp_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        TextInput::make('sftp_remote_path')
                            ->label('Remote Path')
                            ->columnSpanFull()
                            ->placeholder('/uploads/csv'),
                    ]),

                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),
                        Placeholder::make('processed_at')
                            ->label('Processed At')
                            ->content(fn ($record) => $record?->processed_at?->format('M d, Y H:i:s') ?? '-')
                            ->hiddenOn('create'),
                        Textarea::make('error_message')
                            ->label('Error Message')
                            ->columnSpanFull()
                            ->hiddenOn('create')
                            ->disabled(),
                    ]),
            ]);
    }
}
