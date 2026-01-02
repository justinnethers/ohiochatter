<?php

namespace App\Filament\Resources\ContentRevisionResource\Pages;

use App\Filament\Resources\ContentRevisionResource;
use App\Modules\Geography\Actions\Content\ApproveContentRevision;
use App\Modules\Geography\Actions\Content\RejectContentRevision;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewContentRevision extends ViewRecord
{
    protected static string $resource = ContentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve & Apply')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->isPending())
                ->requiresConfirmation()
                ->modalHeading('Approve Revision')
                ->modalDescription('This will apply the proposed changes to the original content. This action cannot be undone.')
                ->action(function () {
                    app(ApproveContentRevision::class)->execute($this->record, auth()->id());
                    $this->refreshFormData(['status', 'reviewed_by', 'reviewed_at']);
                }),

            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->isPending())
                ->form([
                    Textarea::make('notes')
                        ->label('Rejection Reason (optional)')
                        ->placeholder('Explain why this revision was rejected...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    app(RejectContentRevision::class)->execute(
                        $this->record,
                        auth()->id(),
                        $data['notes'] ?? null
                    );
                    $this->refreshFormData(['status', 'reviewed_by', 'reviewed_at', 'review_notes']);
                }),
        ];
    }
}
