<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Models\License;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->fontFamily('mono'),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'expired' => 'warning',
                        'revoked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('currentActivation.domain')
                    ->label('Active Domain')
                    ->placeholder('Not activated')
                    ->limit(30),
                TextColumn::make('validity_days')
                    ->label('Validity')
                    ->suffix(' days')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('domain_changes_used')
                    ->label('Domain Changes')
                    ->formatStateUsing(fn (License $record): string => "{$record->domain_changes_used}/{$record->max_domain_changes}")
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'revoked' => 'Revoked',
                    ]),
            ])
            ->recordActions([
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (License $record): bool => $record->hasActiveActivation())
                    ->action(function (License $record): void {
                        $record->deactivate('Deactivated by admin');
                        Notification::make()
                            ->success()
                            ->title('License deactivated')
                            ->send();
                    }),
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (License $record): bool => $record->status !== 'revoked')
                    ->action(function (License $record): void {
                        $record->deactivate('Revoked by admin');
                        $record->update(['status' => 'revoked']);
                        Notification::make()
                            ->success()
                            ->title('License revoked')
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
