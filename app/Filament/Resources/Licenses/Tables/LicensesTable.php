<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Models\License;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_key')
                    ->label('License Key')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('License key copied')
                    ->fontFamily('mono')
                    ->weight('medium'),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (License $record): ?string => $record->customer_email)
                    ->placeholder('No customer info'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'expired' => 'warning',
                        'revoked' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-m-check-circle',
                        'inactive' => 'heroicon-m-pause-circle',
                        'expired' => 'heroicon-m-clock',
                        'revoked' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),
                TextColumn::make('currentActivation.domain')
                    ->label('Active Domain')
                    ->placeholder('—')
                    ->limit(30)
                    ->tooltip(fn (License $record): ?string => $record->currentActivation?->domain)
                    ->copyable()
                    ->icon('heroicon-m-globe-alt'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Not activated')
                    ->color(fn (License $record): string => match (true) {
                        $record->expires_at === null => 'gray',
                        $record->expires_at->isPast() => 'danger',
                        $record->expires_at->diffInDays(now()) <= 30 => 'warning',
                        default => 'success',
                    })
                    ->description(fn (License $record): ?string => match (true) {
                        $record->expires_at === null => null,
                        $record->expires_at->isPast() => 'Expired '.$record->expires_at->diffForHumans(),
                        default => $record->expires_at->diffForHumans(),
                    }),
                TextColumn::make('domain_changes_used')
                    ->label('Domain Changes')
                    ->formatStateUsing(fn (License $record): string => "{$record->domain_changes_used}/{$record->max_domain_changes}")
                    ->color(fn (License $record): string => $record->canChangeDomain() ? 'gray' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('validity_days')
                    ->label('Validity')
                    ->suffix(' days')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('activated_at')
                    ->label('First Activated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Never')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'revoked' => 'Revoked',
                    ])
                    ->multiple(),
                TernaryFilter::make('has_active_domain')
                    ->label('Domain Bound')
                    ->queries(
                        true: fn ($query) => $query->whereHas('currentActivation'),
                        false: fn ($query) => $query->whereDoesntHave('currentActivation'),
                    ),
                TernaryFilter::make('expiring_soon')
                    ->label('Expiring Soon (30 days)')
                    ->queries(
                        true: fn ($query) => $query->where('expires_at', '>', now())->where('expires_at', '<=', now()->addDays(30)),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()->addDays(30))),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('unbind_domain')
                        ->label('Unbind Domain')
                        ->icon(Heroicon::OutlinedLinkSlash)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Unbind License from Domain')
                        ->modalDescription('This will release the license from its current domain, allowing the customer to activate it on a different domain.')
                        ->visible(fn (License $record): bool => $record->hasActiveActivation())
                        ->action(function (License $record): void {
                            $domain = $record->getActiveDomain();
                            $record->deactivate('Domain unbound by admin');
                            Notification::make()
                                ->success()
                                ->title('Domain unbound')
                                ->body("License released from {$domain}")
                                ->send();
                        }),

                    Action::make('suspend')
                        ->label('Suspend')
                        ->icon(Heroicon::OutlinedPauseCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Suspend License')
                        ->modalDescription('This will temporarily disable the license. The customer will not be able to use it until restored.')
                        ->visible(fn (License $record): bool => in_array($record->status, ['active', 'expired']))
                        ->action(function (License $record): void {
                            $record->deactivate('Suspended by admin');
                            $record->update(['status' => 'inactive']);
                            Notification::make()
                                ->success()
                                ->title('License suspended')
                                ->body('The license has been temporarily disabled.')
                                ->send();
                        }),

                    Action::make('restore')
                        ->label('Restore')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Restore License')
                        ->modalDescription('This will restore the license, making it active again.')
                        ->visible(fn (License $record): bool => in_array($record->status, ['inactive', 'revoked']) && $record->activated_at !== null)
                        ->action(function (License $record): void {
                            $newStatus = $record->expires_at?->isPast() ? 'expired' : 'active';
                            $record->update(['status' => $newStatus]);
                            Notification::make()
                                ->success()
                                ->title('License restored')
                                ->body("License status set to {$newStatus}.")
                                ->send();
                        }),

                    Action::make('extend')
                        ->label('Extend License')
                        ->icon(Heroicon::OutlinedCalendarDateRange)
                        ->color('info')
                        ->visible(fn (License $record): bool => $record->activated_at !== null)
                        ->form([
                            TextInput::make('days')
                                ->label('Days to Add')
                                ->numeric()
                                ->default(30)
                                ->minValue(1)
                                ->maxValue(3650)
                                ->required()
                                ->suffix('days'),
                        ])
                        ->action(function (License $record, array $data): void {
                            $days = (int) $data['days'];
                            $baseDate = $record->expires_at?->isFuture() ? $record->expires_at : now();
                            $newExpiry = $baseDate->addDays($days);

                            $record->update([
                                'expires_at' => $newExpiry,
                                'status' => 'active',
                            ]);

                            Notification::make()
                                ->success()
                                ->title('License extended')
                                ->body("New expiry date: {$newExpiry->format('M d, Y')}")
                                ->send();
                        }),

                    Action::make('reset_domain_changes')
                        ->label('Reset Domain Changes')
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Domain Change Counter')
                        ->modalDescription('This will reset the domain change counter, allowing the customer to switch domains again.')
                        ->visible(fn (License $record): bool => $record->domain_changes_used > 0)
                        ->action(function (License $record): void {
                            $record->update(['domain_changes_used' => 0]);
                            Notification::make()
                                ->success()
                                ->title('Domain changes reset')
                                ->body('The customer can now switch domains again.')
                                ->send();
                        }),

                    Action::make('revoke')
                        ->label('Revoke')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Revoke License')
                        ->modalDescription('This will permanently disable the license. Use this for refunds or policy violations. You can restore it later if needed.')
                        ->visible(fn (License $record): bool => $record->status !== 'revoked')
                        ->action(function (License $record): void {
                            $record->deactivate('Revoked by admin');
                            $record->update(['status' => 'revoked']);
                            Notification::make()
                                ->success()
                                ->title('License revoked')
                                ->body('The license has been permanently disabled.')
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_suspend')
                        ->label('Suspend Selected')
                        ->icon(Heroicon::OutlinedPauseCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (in_array($record->status, ['active', 'expired'])) {
                                    $record->deactivate('Bulk suspended by admin');
                                    $record->update(['status' => 'inactive']);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title("{$count} licenses suspended")
                                ->send();
                        }),
                    BulkAction::make('bulk_revoke')
                        ->label('Revoke Selected')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'revoked') {
                                    $record->deactivate('Bulk revoked by admin');
                                    $record->update(['status' => 'revoked']);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title("{$count} licenses revoked")
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
