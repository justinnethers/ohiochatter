<?php

namespace App\Modules\OhioWordle\Filament\Resources;

use App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource\Pages;
use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use App\Modules\OhioWordle\Models\WordioValidGuess;
use App\Modules\OhioWordle\Services\DictionaryService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class WordioRejectedGuessResource extends Resource
{
    protected static ?string $model = WordioRejectedGuess::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';

    protected static ?string $navigationLabel = 'Rejected Guesses';

    protected static ?string $navigationGroup = 'Games';

    protected static ?string $modelLabel = 'Rejected Guess';

    protected static ?string $pluralModelLabel = 'Rejected Guesses';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guess')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'not_in_dictionary' => 'danger',
                        'wrong_length' => 'warning',
                        'empty' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'not_in_dictionary' => 'Not in Dictionary',
                        'wrong_length' => 'Wrong Length',
                        'empty' => 'Empty',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('word.word')
                    ->label('Puzzle')
                    ->default('—'),

                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->default('Guest'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->options([
                        'not_in_dictionary' => 'Not in Dictionary',
                        'wrong_length' => 'Wrong Length',
                        'empty' => 'Empty',
                    ]),

                SelectFilter::make('user_type')
                    ->label('User Type')
                    ->options([
                        'registered' => 'Registered',
                        'guest' => 'Guest',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'registered') {
                            $query->whereNotNull('user_id');
                        } elseif ($data['value'] === 'guest') {
                            $query->whereNull('user_id');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WordioRejectedGuess $record): bool => $record->reason === WordioRejectedGuess::REASON_NOT_IN_DICTIONARY)
                    ->requiresConfirmation()
                    ->modalHeading(fn (WordioRejectedGuess $record): string => "Approve \"{$record->guess}\"?")
                    ->modalDescription('This word will be added to the valid guesses dictionary.')
                    ->action(function (WordioRejectedGuess $record): void {
                        WordioValidGuess::approve($record->guess, auth()->id());
                        app(DictionaryService::class)->clearCache();
                        $record->delete();

                        Notification::make()
                            ->title('Word approved')
                            ->body("\"{$record->guess}\" has been added to the dictionary.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve selected words?')
                        ->modalDescription('Selected words with "Not in Dictionary" reason will be added to the valid guesses dictionary.')
                        ->action(function (Collection $records): void {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->reason === WordioRejectedGuess::REASON_NOT_IN_DICTIONARY) {
                                    WordioValidGuess::approve($record->guess, auth()->id());
                                    $record->delete();
                                    $approved++;
                                }
                            }

                            if ($approved > 0) {
                                app(DictionaryService::class)->clearCache();

                                Notification::make()
                                    ->title('Words approved')
                                    ->body("{$approved} word(s) have been added to the dictionary.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('No words approved')
                                    ->body('No eligible words were selected (only "Not in Dictionary" entries can be approved).')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWordioRejectedGuesses::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('reason', 'not_in_dictionary')
            ->where('created_at', '>=', now()->subDay())
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
