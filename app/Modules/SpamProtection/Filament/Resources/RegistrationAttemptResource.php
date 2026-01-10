<?php

namespace App\Modules\SpamProtection\Filament\Resources;

use App\Modules\SpamProtection\Filament\Resources\RegistrationAttemptResource\Pages;
use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use App\Modules\SpamProtection\Models\RegistrationAttempt;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationAttemptResource extends Resource
{
    protected static ?string $model = RegistrationAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Spam Protection';

    protected static ?string $navigationLabel = 'Registration Log';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('ip_address')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('email')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('username')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => 'Success',
                        'blocked_domain' => 'Blocked Domain',
                        'blocked_tld' => 'Blocked TLD',
                        'blocked_disposable' => 'Disposable Email',
                        'blocked_ip_rate' => 'Rate Limited',
                        'blocked_pattern' => 'Pattern Detected',
                        'blocked_stopforumspam' => 'StopForumSpam',
                        'blocked_honeypot' => 'Honeypot',
                        default => $state,
                    }),

                TextColumn::make('block_reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->block_reason)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_agent')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Successful',
                        'blocked_domain' => 'Blocked Domain',
                        'blocked_tld' => 'Blocked TLD',
                        'blocked_disposable' => 'Disposable Email',
                        'blocked_ip_rate' => 'IP Rate Limited',
                        'blocked_pattern' => 'Pattern Detected',
                        'blocked_stopforumspam' => 'StopForumSpam',
                        'blocked_honeypot' => 'Honeypot Triggered',
                    ]),
                Filter::make('blocked_only')
                    ->label('Blocked Only')
                    ->query(fn (Builder $query) => $query->where('status', '!=', 'success')),
                Filter::make('today')
                    ->label('Today Only')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\Action::make('block_domain')
                    ->label('Block Domain')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->email && $record->status === 'success')
                    ->requiresConfirmation()
                    ->modalHeading('Block Email Domain')
                    ->modalDescription(fn ($record) => 'Are you sure you want to block the domain from: ' . $record->email . '?')
                    ->action(function ($record) {
                        $domain = substr(strrchr($record->email, '@'), 1);
                        BlockedEmailDomain::firstOrCreate(
                            ['domain' => strtolower($domain)],
                            [
                                'reason' => 'Blocked from admin review',
                                'type' => 'manual',
                                'added_by' => auth()->id(),
                            ]
                        );
                    }),

                Tables\Actions\Action::make('view_details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Registration Attempt Details')
                    ->modalContent(fn ($record) => view('filament.modals.registration-attempt-details', ['record' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrationAttempts::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $blockedToday = static::getModel()::query()
            ->where('status', '!=', 'success')
            ->whereDate('created_at', today())
            ->count();

        return $blockedToday ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
