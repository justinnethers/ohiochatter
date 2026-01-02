<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentRevisionResource\Pages;
use App\Models\ContentRevision;
use App\Modules\Geography\Actions\Content\ApproveContentRevision;
use App\Modules\Geography\Actions\Content\RejectContentRevision;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentRevisionResource extends Resource
{
    protected static ?string $model = ContentRevision::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Pending Revisions';

    protected static ?string $modelLabel = 'Revision';

    protected static ?string $pluralModelLabel = 'Revisions';

    protected static ?string $navigationGroup = 'Content';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content.title')
                    ->label('Original Content')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Proposed Title')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('author.username')
                    ->label('Submitted By')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContentRevision $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Approve Revision')
                    ->modalDescription('This will apply the proposed changes to the original content.')
                    ->action(function (ContentRevision $record) {
                        app(ApproveContentRevision::class)->execute($record, auth()->id());
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ContentRevision $record) => $record->isPending())
                    ->form([
                        Textarea::make('notes')
                            ->label('Rejection Reason (optional)')
                            ->placeholder('Explain why this revision was rejected...')
                            ->rows(3),
                    ])
                    ->action(function (ContentRevision $record, array $data) {
                        app(RejectContentRevision::class)->execute(
                            $record,
                            auth()->id(),
                            $data['notes'] ?? null
                        );
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentRevisions::route('/'),
            'view' => Pages\ViewContentRevision::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
