<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Models\License;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('License Information')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('license_key')
                            ->default(fn () => License::generateLicenseKey())
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->readOnlyOn('edit'),
                        Select::make('status')
                            ->options([
                                'inactive' => 'Inactive',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'revoked' => 'Revoked',
                            ])
                            ->default('inactive')
                            ->required(),
                        TextInput::make('validity_days')
                            ->label('Validity (Days)')
                            ->numeric()
                            ->default(365)
                            ->required()
                            ->helperText('Duration from first activation'),
                    ]),
                Section::make('Customer Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('Domain Settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('max_domain_changes')
                            ->label('Max Domain Changes')
                            ->numeric()
                            ->default(3)
                            ->required(),
                        Placeholder::make('domain_changes_used')
                            ->label('Domain Changes Used')
                            ->content(fn (?License $record): string => $record?->domain_changes_used ?? '0')
                            ->visibleOn('edit'),
                    ]),
                Section::make('Activation Status')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('activated_at')
                            ->label('First Activated')
                            ->content(fn (?License $record): string => $record?->activated_at?->format('M d, Y H:i') ?? 'Not activated'),
                        Placeholder::make('expires_at')
                            ->label('Expires At')
                            ->content(fn (?License $record): string => $record?->expires_at?->format('M d, Y H:i') ?? 'N/A'),
                        Placeholder::make('active_domain')
                            ->label('Active Domain')
                            ->content(fn (?License $record): string => $record?->getActiveDomain() ?? 'None'),
                    ])
                    ->visibleOn('edit'),
                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
