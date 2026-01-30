<?php

namespace App\Modules\OhioWordle\Filament\Resources;

use App\Modules\OhioWordle\Filament\Resources\WordioValidGuessResource\Pages;
use App\Modules\OhioWordle\Models\WordioValidGuess;
use App\Modules\OhioWordle\Services\DictionaryService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WordioValidGuessResource extends Resource
{
    protected static ?string $model = WordioValidGuess::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Valid Guesses';

    protected static ?string $navigationGroup = 'Games';

    protected static ?string $modelLabel = 'Valid Guess';

    protected static ?string $pluralModelLabel = 'Valid Guesses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('word')
                    ->required()
                    ->maxLength(20)
                    ->helperText('The word to add to the valid guesses dictionary (will be uppercased automatically)')
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('word')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('approvedBy.username')
                    ->label('Approved By')
                    ->default('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => app(DictionaryService::class)->clearCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn () => app(DictionaryService::class)->clearCache()),
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
            'index' => Pages\ListWordioValidGuesses::route('/'),
            'create' => Pages\CreateWordioValidGuess::route('/create'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
