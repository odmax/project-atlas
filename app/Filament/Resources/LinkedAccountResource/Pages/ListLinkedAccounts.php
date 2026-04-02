<?php

namespace App\Filament\Resources\LinkedAccountResource\Pages;

use App\Filament\Resources\LinkedAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinkedAccounts extends ListRecords
{
    protected static string $resource = LinkedAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
