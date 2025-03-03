<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuzzleResource\Pages;
use App\Filament\Resources\PuzzleResource\RelationManagers;
use App\Models\Puzzle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PuzzleResource extends Resource
{
    protected static ?string $model = Puzzle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('answer')->required(),
                TextInput::make('publish_date')->unique()->required(),
                FileUpload::make('image_path')
                    ->directory('puzzles')
                    ->image()
                    ->imageEditor()
                    ->required(),
                TextInput::make('word_count')->required(),
                Textarea::make('hint')->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('answer')
                    ->description(fn(Puzzle $puzzle): string => $puzzle->hint, 'limit: 5'),
                Tables\Columns\TextColumn::make('publish_date')->date()

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePuzzles::route('/'),
        ];
    }
}
