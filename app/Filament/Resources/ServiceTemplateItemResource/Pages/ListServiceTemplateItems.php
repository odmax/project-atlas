<?php

namespace App\Filament\Resources\ServiceTemplateItemResource\Pages;

use App\Filament\Resources\ServiceTemplateItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceTemplateItems extends ListRecords
{
    protected static string $resource = ServiceTemplateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
