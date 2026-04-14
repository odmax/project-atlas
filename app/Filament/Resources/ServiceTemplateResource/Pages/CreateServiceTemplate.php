<?php

namespace App\Filament\Resources\ServiceTemplateResource\Pages;

use App\Filament\Resources\ServiceTemplateResource;
use Filament\Forms;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceTemplate extends CreateRecord
{
    protected static string $resource = ServiceTemplateResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
