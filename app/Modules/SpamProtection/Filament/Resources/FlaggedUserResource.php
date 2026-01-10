<?php

namespace App\Modules\SpamProtection\Filament\Resources;

use App\Models\User;
use App\Modules\SpamProtection\Filament\Resources\FlaggedUserResource\Pages;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FlaggedUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Spam Protection';

    protected static ?string $navigationLabel = 'Flagged Users';

    protected static ?string $modelLabel = 'Flagged User';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_flagged_spam', true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->username),

                TextColumn::make('email')
                    ->searchable()
                    ->limit(30)
                    ->copyable(),

                TextColumn::make('spam_flag_reason')
                    ->label('Flag Reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->spam_flag_reason)
                    ->wrap(),

                TextColumn::make('post_count')
                    ->label('Posts')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'gray' : 'warning'),

                TextColumn::make('spam_flagged_at')
                    ->label('Flagged')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('spam_flag_reason')
                    ->label('Flag Reason')
                    ->options([
                        'blocked_domain' => 'Blocked Domain',
                        'blocked_tld' => 'Blocked TLD',
                        'blocked_disposable' => 'Disposable Email',
                        'blocked_pattern' => 'Pattern Detected',
                        'blocked_stopforumspam' => 'StopForumSpam',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }

                        return $query->where('spam_flag_reason', 'like', "%{$data['value']}%");
                    }),

                Filter::make('zero_posts')
                    ->label('Zero Posts Only')
                    ->query(fn (Builder $query) => $query->where('post_count', 0)),

                Filter::make('has_posts')
                    ->label('Has Posts')
                    ->query(fn (Builder $query) => $query->where('post_count', '>', 0)),
            ])
            ->actions([
                Action::make('unflag')
                    ->label('Unflag')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Unflag User')
                    ->modalDescription(fn ($record) => "Are you sure you want to unflag {$record->username}? This will mark them as a legitimate user.")
                    ->action(function ($record) {
                        $record->update([
                            'is_flagged_spam' => false,
                            'spam_flag_reason' => null,
                            'spam_flagged_at' => null,
                        ]);

                        Notification::make()
                            ->title('User unflagged')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->modalHeading('Delete User')
                    ->modalDescription(fn ($record) => "Are you sure you want to permanently delete {$record->username}? This cannot be undone."),
            ])
            ->bulkActions([
                BulkAction::make('unflag')
                    ->label('Unflag Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Unflag Selected Users')
                    ->modalDescription('Are you sure you want to unflag these users?')
                    ->action(function (Collection $records) {
                        $records->each(fn ($record) => $record->update([
                            'is_flagged_spam' => false,
                            'spam_flag_reason' => null,
                            'spam_flagged_at' => null,
                        ]));

                        Notification::make()
                            ->title('Users unflagged')
                            ->body($records->count() . ' users have been unflagged.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('Are you sure you want to permanently delete these users? This cannot be undone.'),
                ]),
            ])
            ->headerActions([
                Action::make('delete_all_flagged')
                    ->label('Delete All Flagged')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete All Flagged Users')
                    ->modalDescription(function () {
                        $count = User::where('is_flagged_spam', true)->count();

                        return "Are you sure you want to permanently delete all {$count} flagged users? This action cannot be undone.";
                    })
                    ->action(function () {
                        $count = User::where('is_flagged_spam', true)->count();
                        User::where('is_flagged_spam', true)->delete();

                        Notification::make()
                            ->title('All flagged users deleted')
                            ->body("{$count} users have been permanently deleted.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('spam_flagged_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlaggedUsers::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::where('is_flagged_spam', true)->count();

        return $count ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
