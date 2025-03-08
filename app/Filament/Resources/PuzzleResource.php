<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuzzleResource\Pages;
use App\Filament\Resources\PuzzleResource\RelationManagers\UserProgressRelationManager;
use App\Models\Puzzle;
use App\Models\UserGameProgress;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PuzzleResource extends Resource
{
    protected static ?string $model = Puzzle::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $recordTitleAttribute = 'answer';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('answer')->required()->columnSpan(2),

                DatePicker::make('publish_date')
                    ->required()
                    ->unique(ignorable: fn($record) => $record)
                    ->columnSpan(2),

                FileUpload::make('image_path')
                    ->directory('puzzles')
                    ->image()
                    ->imageEditor()
                    ->required()
                    ->live()
                    ->columnSpan(2),

                View::make('filament.forms.components.pixelated-image-preview')
                    ->visible(fn($get) => filled($get('image_path')))
                    ->columnSpan(2),

                TextInput::make('word_count')->required(),
                TextInput::make('category')->required(),
                Textarea::make('hint')->required(),
                Textarea::make('hint_2')->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('answer')
                    ->description(fn(Puzzle $puzzle): string => $puzzle->hint, 'limit: 5'),
                Tables\Columns\TextColumn::make('publish_date')->date(),
                Tables\Columns\TextColumn::make('players_count')
                    ->label('Players')
                    ->getStateUsing(function (Puzzle $puzzle): int {
                        return UserGameProgress::where('puzzle_id', $puzzle->id)->count();
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withCount('userProgress as players_count')
                            ->orderBy('players_count', $direction);
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('publish_date', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            UserProgressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPuzzles::route('/'),
            'create' => Pages\CreatePuzzle::route('/create'),
            'view' => Pages\ViewPuzzle::route('/{record}'),
            'edit' => Pages\EditPuzzle::route('/{record}/edit'),
        ];
    }
}
