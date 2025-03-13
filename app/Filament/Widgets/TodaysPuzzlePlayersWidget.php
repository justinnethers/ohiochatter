<?php

namespace App\Filament\Widgets;

use App\Models\Puzzle;
use App\Models\UserGameProgress;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class TodaysPuzzlePlayersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $todaysPuzzle = Puzzle::where('publish_date', Carbon::today()->toDateString())->first();

        return $table
            ->heading('Today\'s Puzzle Players')
            ->description($todaysPuzzle ? 'Players who have attempted today\'s puzzle: ' . $todaysPuzzle->answer : 'No puzzle available for today')
            ->query(
                $todaysPuzzle
                    ? UserGameProgress::query()
                    ->where('puzzle_id', $todaysPuzzle->id)
                    ->with('user')
                    : UserGameProgress::query()->whereRaw('1 = 0') // Empty query if no puzzle found
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\IconColumn::make('solved')
                    ->boolean()
                    ->label('Solved'),
                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts'),
                Tables\Columns\TextColumn::make('guesses_taken')
                    ->label('Guesses Taken'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('previous_guesses')
                    ->label('Guesses')
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
            ->defaultSort('completed_at', 'desc');
    }
}
