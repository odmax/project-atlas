<?php

namespace App\Filament\Resources\ServiceTemplateResource\Pages;

use App\Filament\Resources\ServiceTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditServiceTemplate extends EditRecord
{
    protected static string $resource = ServiceTemplateResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
