<?php

namespace App\Modules\SpamProtection\Filament\Resources;

use App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource\Pages;
use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BlockedEmailDomainResource extends Resource
{
    protected static ?string $model = BlockedEmailDomain::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Spam Protection';

    protected static ?string $navigationLabel = 'Blocked Domains';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('domain')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('example.com')
                ->helperText('Enter domain without @ symbol')
                ->columnSpan(2),

            Select::make('type')
                ->options([
                    'manual' => 'Manual Block',
                    'disposable' => 'Disposable Email',
                    'stopforumspam' => 'StopForumSpam',
                ])
                ->default('manual')
                ->required(),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),

            Textarea::make('reason')
                ->placeholder('Reason for blocking this domain')
                ->rows(2)
                ->columnSpan(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'danger',
                        'disposable' => 'warning',
                        'stopforumspam' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->reason),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('addedByUser.username')
                    ->label('Added By')
                    ->default('System'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'manual' => 'Manual',
                        'disposable' => 'Disposable',
                        'stopforumspam' => 'StopForumSpam',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockedEmailDomains::route('/'),
            'create' => Pages\CreateBlockedEmailDomain::route('/create'),
            'edit' => Pages\EditBlockedEmailDomain::route('/{record}/edit'),
        ];
    }
}
