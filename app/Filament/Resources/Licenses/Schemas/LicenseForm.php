<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Models\License;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('License Details')
                    ->icon(Heroicon::OutlinedKey)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_id')
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            TextInput::make('license_key')
                                ->default(fn () => License::generateLicenseKey())
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->readOnlyOn('edit')
                                ->suffixAction(
                                    Action::make('regenerate')
                                        ->icon('heroicon-m-arrow-path')
                                        ->action(fn ($set) => $set('license_key', License::generateLicenseKey()))
                                        ->hidden(fn (string $operation): bool => $operation === 'edit')
                                ),
                        ]),
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->options([
                                    'inactive' => 'Inactive',
                                    'active' => 'Active',
                                    'expired' => 'Expired',
                                    'revoked' => 'Revoked',
                                ])
                                ->default('inactive')
                                ->required()
                                ->native(false)
                                ->helperText('Use actions in the table for status changes'),
                            TextInput::make('validity_days')
                                ->label('Validity Period')
                                ->numeric()
                                ->default(365)
                                ->required()
                                ->suffix('days')
                                ->helperText('Duration from first activation'),
                        ]),
                    ]),

                Section::make('Customer Information')
                    ->icon(Heroicon::OutlinedUser)
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('customer_name')
                                ->label('Name')
                                ->maxLength(255)
                                ->placeholder('John Doe'),
                            TextInput::make('customer_email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255)
                                ->placeholder('john@example.com'),
                        ]),
                    ]),

                Section::make('Domain Settings')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('max_domain_changes')
                                ->label('Max Domain Changes')
                                ->numeric()
                                ->default(3)
                                ->required()
                                ->minValue(0)
                                ->helperText('How many times can the customer switch domains'),
                            TextInput::make('domain_changes_used')
                                ->label('Domain Changes Used')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->minValue(0)
                                ->helperText('Reset via table action if needed'),
                        ]),
                    ]),

                Section::make('Activation Status')
                    ->icon(Heroicon::OutlinedSignal)
                    ->collapsible()
                    ->visibleOn('edit')
                    ->schema([
                        Placeholder::make('status_badge')
                            ->label('Current Status')
                            ->content(function (?License $record): \Illuminate\Support\HtmlString {
                                if (! $record) {
                                    return new \Illuminate\Support\HtmlString('—');
                                }

                                $colors = [
                                    'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'expired' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                    'revoked' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                ];
                                $color = $colors[$record->status] ?? $colors['inactive'];

                                return new \Illuminate\Support\HtmlString(
                                    "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}\">".ucfirst($record->status).'</span>'
                                );
                            }),

                        Grid::make(2)->schema([
                            Fieldset::make('Dates')
                                ->schema([
                                    Placeholder::make('activated_at_display')
                                        ->label('First Activated')
                                        ->content(fn (?License $record): string => $record?->activated_at?->format('M d, Y H:i') ?? 'Never'),
                                    Placeholder::make('expires_at_display')
                                        ->label('Expires At')
                                        ->content(function (?License $record): string {
                                            if (! $record?->expires_at) {
                                                return 'Not activated yet';
                                            }
                                            $date = $record->expires_at->format('M d, Y');
                                            $diff = $record->expires_at->diffForHumans();

                                            return "{$date} ({$diff})";
                                        }),
                                ]),

                            Fieldset::make('Current Activation')
                                ->schema([
                                    Placeholder::make('active_domain_display')
                                        ->label('Domain')
                                        ->content(fn (?License $record): string => $record?->getActiveDomain() ?? 'No active domain'),
                                    Placeholder::make('activation_ip')
                                        ->label('IP Address')
                                        ->content(fn (?License $record): string => $record?->currentActivation?->ip_address ?? '—'),
                                    Placeholder::make('activation_date')
                                        ->label('Activated On')
                                        ->content(fn (?License $record): string => $record?->currentActivation?->activated_at?->format('M d, Y H:i') ?? '—'),
                                ]),
                        ]),

                        Fieldset::make('Domain Changes')
                            ->schema([
                                Grid::make(2)->schema([
                                    Placeholder::make('domain_changes_info')
                                        ->label('Usage')
                                        ->content(fn (?License $record): string => $record
                                            ? "{$record->domain_changes_used} of {$record->max_domain_changes} used"
                                            : '—'),
                                    Placeholder::make('remaining_changes')
                                        ->label('Remaining')
                                        ->content(fn (?License $record): string => $record
                                            ? ($record->canChangeDomain() ? "{$record->remainingDomainChanges()} changes left" : 'No changes remaining')
                                            : '—'),
                                ]),
                            ]),
                    ]),

                Section::make('Notes')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Internal notes about this license...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->icon(Heroicon::OutlinedClock)
                    ->collapsible()
                    ->collapsed()
                    ->visibleOn('edit')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('created_at_display')
                                ->label('Created')
                                ->content(fn (?License $record): string => $record?->created_at?->format('M d, Y H:i') ?? '—'),
                            Placeholder::make('updated_at_display')
                                ->label('Last Updated')
                                ->content(fn (?License $record): string => $record?->updated_at?->format('M d, Y H:i') ?? '—'),
                        ]),
                    ]),
            ]);
    }
}
