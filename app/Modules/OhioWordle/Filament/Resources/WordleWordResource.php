<?php

namespace App\Modules\OhioWordle\Filament\Resources;

use App\Modules\OhioWordle\Filament\Resources\WordleWordResource\Pages;
use App\Modules\OhioWordle\Models\WordleUserProgress;
use App\Modules\OhioWordle\Models\WordleWord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WordleWordResource extends Resource
{
    protected static ?string $model = WordleWord::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Games';

    protected static ?string $recordTitleAttribute = 'word';

    protected static ?string $modelLabel = 'Wordle Word';

    protected static ?string $pluralModelLabel = 'Wordle Words';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('word')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The word players will try to guess (will be uppercased automatically)')
                    ->columnSpan(2),

                DatePicker::make('publish_date')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->helperText('Date when this word will be the daily puzzle')
                    ->columnSpan(1),

                TextInput::make('category')
                    ->maxLength(255)
                    ->helperText('Optional category (e.g., "Cities", "Famous Ohioans")')
                    ->columnSpan(1),

                TextInput::make('hint')
                    ->maxLength(255)
                    ->helperText('Optional hint to help players')
                    ->columnSpan(2),

                Select::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ])
                    ->default('medium')
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Inactive words will not be used as daily puzzles')
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('word')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('word_length')
                    ->label('Length')
                    ->sortable(),
                Tables\Columns\TextColumn::make('publish_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('players_count')
                    ->label('Players')
                    ->getStateUsing(function (WordleWord $word): int {
                        return WordleUserProgress::where('word_id', $word->id)->count();
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withCount('userProgress as players_count')
                            ->orderBy('players_count', $direction);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('publish_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWordleWords::route('/'),
            'create' => Pages\CreateWordleWord::route('/create'),
            'edit' => Pages\EditWordleWord::route('/{record}/edit'),
        ];
    }
}
