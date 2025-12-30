<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use App\Modules\Geography\Actions\Content\PublishContent;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContent extends ViewRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => is_null($this->record->published_at))
                ->requiresConfirmation()
                ->modalHeading('Publish Guide')
                ->modalDescription('Are you sure you want to publish this guide? It will be visible to all users.')
                ->action(function () {
                    app(PublishContent::class)->execute($this->record);
                    $this->refreshFormData(['published_at']);
                }),

            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => ! is_null($this->record->published_at))
                ->requiresConfirmation()
                ->modalHeading('Unpublish Guide')
                ->modalDescription('Are you sure you want to unpublish this guide? It will no longer be visible to users.')
                ->action(function () {
                    $this->record->update(['published_at' => null]);
                    $this->refreshFormData(['published_at']);
                }),

            Actions\EditAction::make(),
        ];
    }
}
