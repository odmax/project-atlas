<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\LinkedAccount;
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
                    LinkedAccount::create([
                        'user_id' => $record->id,
                        'connector_id' => $item->connector_id,
                        'account_type' => $item->account_type,
                        'external_id' => null,
                        'external_username' => $record->primary_email,
                        'external_email' => $record->primary_email,
                        'desired_state' => 'active',
                        'actual_state' => 'pending',
                        'provisioning_status' => 'pending',
                        'external_role' => $item->default_role,
                        'metadata_json' => $item->metadata_json,
                    ]);
                }
            }
        }
    }
}
