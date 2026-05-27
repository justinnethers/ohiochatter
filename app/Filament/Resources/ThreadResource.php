<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThreadResource\Pages;
use App\Models\Thread;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thread')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('forum_id')
                            ->label('Forum')
                            ->relationship('forum', 'name')
                            ->required(),

                        RichEditor::make('body')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('locked'),

                        Toggle::make('hidden'),
                    ])
                    ->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('keywords')
                            ->maxLength(255),

                        TextInput::make('meta_title')
                            ->maxLength(255),

                        Textarea::make('meta_description')
                            ->rows(2),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Author')
                    ->sortable(),

                Tables\Columns\TextColumn::make('forum.name')
                    ->label('Forum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('replies_count')
                    ->label('Replies')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->sortable(),

                Tables\Columns\IconColumn::make('locked')
                    ->boolean(),

                Tables\Columns\IconColumn::make('hidden')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('forum')
                    ->relationship('forum', 'name')
                    ->preload(),

                TernaryFilter::make('locked'),

                TernaryFilter::make('hidden'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('toggleLocked')
                    ->label(fn (Thread $record): string => $record->locked ? 'Unlock' : 'Lock')
                    ->icon(fn (Thread $record): string => $record->locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->requiresConfirmation()
                    ->action(fn (Thread $record) => $record->update(['locked' => ! $record->locked])),
                Action::make('toggleHidden')
                    ->label(fn (Thread $record): string => $record->hidden ? 'Unhide' : 'Hide')
                    ->icon(fn (Thread $record): string => $record->hidden ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->requiresConfirmation()
                    ->action(fn (Thread $record) => $record->update(['hidden' => ! $record->hidden])),
            ])
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
            'index' => Pages\ListThreads::route('/'),
            'view' => Pages\ViewThread::route('/{record}'),
            'edit' => Pages\EditThread::route('/{record}/edit'),
        ];
    }
}
