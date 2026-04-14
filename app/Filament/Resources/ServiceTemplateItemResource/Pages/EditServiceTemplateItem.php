<?php

namespace App\Filament\Resources\ServiceTemplateItemResource\Pages;

use App\Filament\Resources\ServiceTemplateItemResource;
use Filament\Resources\Pages\EditRecord;

class EditServiceTemplateItem extends EditRecord
{
    protected static string $resource = ServiceTemplateItemResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
