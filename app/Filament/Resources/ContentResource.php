<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Modules\Geography\Actions\Content\PublishContent;
use Filament\Forms\Components\DateTimePicker;
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

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Guides';

    protected static ?string $modelLabel = 'Guide';

    protected static ?string $pluralModelLabel = 'Guides';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('content_category_id')
                            ->label('Category')
                            ->options(ContentCategory::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Select::make('content_type_id')
                            ->label('Type')
                            ->options(ContentType::orderBy('name')->pluck('name', 'id'))
                            ->required(),

                        Textarea::make('excerpt')
                            ->label('Summary')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('body')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextInput::make('location_display')
                            ->label('Location')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn ($record) => $record?->locatable?->name ?? 'Not set'),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('featured')
                            ->label('Featured Guide'),

                        DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->helperText('Leave empty to keep as draft'),
                    ])
                    ->columns(2),

                Section::make('SEO')
                    ->schema([
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
                    ->limit(50),

                Tables\Columns\TextColumn::make('author.username')
                    ->label('Author')
                    ->sortable(),

                Tables\Columns\TextColumn::make('contentCategory.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('locatable.name')
                    ->label('Location')
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn (Content $record): string => $record->published_at ? 'Published' : 'Pending Review')
                    ->colors([
                        'warning' => 'Pending Review',
                        'success' => 'Published',
                    ]),

                Tables\Columns\IconColumn::make('featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Published')
                    ->falseLabel('Pending Review')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('published_at'),
                        false: fn ($query) => $query->whereNull('published_at'),
                    ),

                SelectFilter::make('content_category_id')
                    ->label('Category')
                    ->options(ContentCategory::orderBy('name')->pluck('name', 'id')),

                TernaryFilter::make('featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('publish')
                    ->label('Approve & Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Content $record): bool => is_null($record->published_at))
                    ->requiresConfirmation()
                    ->modalHeading('Approve & Publish Guide')
                    ->modalDescription('Are you sure you want to publish this guide? It will be visible to all users.')
                    ->action(function (Content $record) {
                        app(PublishContent::class)->execute($record);
                    }),
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Content $record): bool => ! is_null($record->published_at))
                    ->requiresConfirmation()
                    ->action(function (Content $record) {
                        $record->update(['published_at' => null]);
                    }),
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
            'index' => Pages\ListContents::route('/'),
            'view' => Pages\ViewContent::route('/{record}'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('published_at')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
