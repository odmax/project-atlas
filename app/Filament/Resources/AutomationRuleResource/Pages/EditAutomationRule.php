<?php

namespace App\Filament\Resources\AutomationRuleResource\Pages;

use App\Filament\Resources\AutomationRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditAutomationRule extends EditRecord
{
    protected static string $resource = AutomationRuleResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
