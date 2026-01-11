<?php

namespace App\Modules\BuckEYE\Filament\Widgets;

use App\Modules\BuckEYE\Models\AnonymousGameProgress;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameProgress;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TodaysPuzzlePlayersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $todaysPuzzle = Puzzle::where('publish_date', Carbon::today()->toDateString())->first();

        if (!$todaysPuzzle) {
            // No puzzle today, return empty table
            return $table
                ->heading('Today\'s Puzzle Players')
                ->description('No puzzle available for today')
                ->query(UserGameProgress::query()->whereRaw('1 = 0')); // Empty query
        }

        // Use union of both authenticated and anonymous users
        $authenticatedUsersQuery = UserGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->with('user')
            ->select(
                'id',
                'user_id',
                DB::raw('NULL as session_id'),
                'solved',
                'attempts',
                'guesses_taken',
                'completed_at',
                'previous_guesses',
                DB::raw("'Registered' as user_type")
            );

        $anonymousUsersQuery = AnonymousGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->select(
                'id',
                DB::raw('NULL as user_id'),
                'session_id',
                'solved',
                'attempts',
                'guesses_taken',
                'completed_at',
                'previous_guesses',
                DB::raw("'Guest' as user_type")
            );

        // Create a base query for the combined results
        $baseQuery = $authenticatedUsersQuery->union($anonymousUsersQuery);

        return $table
            ->heading('Today\'s Puzzle Players')
            ->description('Players who have attempted today\'s puzzle: ' . $todaysPuzzle->answer)
            ->query(
            // Wrap the union query
                UserGameProgress::query()
                    ->from(DB::raw("({$baseQuery->toSql()}) as combined_users"))
                    ->mergeBindings($baseQuery->getQuery())
            )
            ->columns([
                Tables\Columns\TextColumn::make('user_type')
                    ->label('User Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Registered' => 'success',
                        'Guest' => 'info',
                    }),
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->getStateUsing(function ($record): string {
                        if ($record->user_type === 'Registered' && $record->user) {
                            return $record->user->username;
                        }
                        return 'Guest ' . substr($record->session_id, 0, 8);
                    }),
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
                Tables\Filters\SelectFilter::make('user_type')
                    ->options([
                        'Registered' => 'Registered Users',
                        'Guest' => 'Guest Users',
                    ]),
            ])
            ->defaultSort('completed_at', 'desc');
    }
}
