<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\ServiceAssignment;
use App\Models\ServiceTemplate;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->data;

        if (!empty($data['service_template_id'])) {
            $template = ServiceTemplate::with('items')->find($data['service_template_id']);
            
            if ($template && $template->items->isNotEmpty()) {
                foreach ($template->items as $item) {
                    ServiceAssignment::create([
                        'user_id' => $record->id,
                        'service_template_id' => $template->id,
                        'connector_id' => $item->connector_id,
                        'account_type' => $item->account_type,
                        'desired_state' => 'active',
                        'default_role' => $item->default_role,
                        'status' => 'pending',
                        'metadata_json' => $item->metadata_json,
                    ]);
                }
            }
        }
    }
}
