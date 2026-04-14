<?php

namespace App\Filament\Resources\SyncJobResource\Pages;

use App\Filament\Resources\SyncJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSyncJob extends ViewRecord
{
    protected static string $resource = SyncJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry')
                ->label('Queue Retry')
                ->visible(fn ($record) => $record->canRetry())
                ->action(function ($record) {
                    $record->markAsQueuedForRetry();
                }),
        ];
    }
}
