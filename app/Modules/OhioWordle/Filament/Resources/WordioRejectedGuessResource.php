<?php

namespace App\Modules\OhioWordle\Filament\Resources;

use App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource\Pages;
use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
