<?php

namespace App\Modules\BuckEYE\Filament\Resources\PuzzleResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UserProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'userProgress';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username'),
                Tables\Columns\IconColumn::make('solved')
                    ->boolean()
                    ->label('Solved'),
                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('previous_guesses')
                    ->label('Guesses')
                    ->columnSpan(2) // Make this column span 2 columns worth of space
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode(', ', $state);
                        }
                        if (is_string($state) && $state) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                return implode(', ', $decoded);
                            }
                            return $state;
                        }
                        return '';
                    })
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('solved')
                    ->options([
                        '1' => 'Solved',
                        '0' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
