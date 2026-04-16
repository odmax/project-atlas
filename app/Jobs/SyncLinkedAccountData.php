<?php

namespace App\Jobs;

use App\Models\LinkedAccount;
use App\Services\ApiClientFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncLinkedAccountData implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public function __construct(
        public LinkedAccount $linkedAccount,
        public string $action = 'create'
    ) {}

    public function handle(): void
    {
        $connector = $this->linkedAccount->connector;
        
        if (!$connector || !$connector->is_active) {
            Log::warning('Connector not found or inactive', [
                'linked_account_id' => $this->linkedAccount->id,
                'connector_id' => $this->linkedAccount->connector_id,
            ]);
            return;
        }

        try {
            $client = ApiClientFactory::create($connector);
            
            match ($this->action) {
                'create' => $this->handleCreate($client),
                'update' => $this->handleUpdate($client),
                'delete' => $this->handleDelete($client),
                default => Log::warning('Unknown action', ['action' => $this->action]),
            };

            $this->linkedAccount->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'actual_state' => $this->linkedAccount->desired_state,
                'provisioning_status' => 'active',
            ]);

        } catch (\Exception $e) {
            Log::error('SyncLinkedAccountData failed', [
                'linked_account_id' => $this->linkedAccount->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            $this->linkedAccount->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'provisioning_status' => 'failed',
            ]);
        }
    }

    protected function handleCreate($client): void
    {
        $account = $this->linkedAccount;

        match ($account->account_type) {
            'cpanel_email' => $this->handleCpanelEmail($client, 'create'),
            'cpanel_ftp' => $this->handleCpanelFtp($client, 'create'),
            'wordpress_user' => $this->handleWordPressUser($client, 'create'),
            default => Log::warning('Unknown account type', ['type' => $account->account_type]),
        };
    }

    protected function handleUpdate($client): void
    {
        $account = $this->linkedAccount;

        match ($account->account_type) {
            'cpanel_email' => $this->handleCpanelEmail($client, 'update'),
            'cpanel_ftp' => $this->handleCpanelFtp($client, 'update'),
            'wordpress_user' => $this->handleWordPressUser($client, 'update'),
            default => Log::warning('Unknown account type', ['type' => $account->account_type]),
        };
    }

    protected function handleDelete($client): void
    {
        $account = $this->linkedAccount;

        match ($account->account_type) {
            'cpanel_email' => $this->handleCpanelEmail($client, 'delete'),
            'cpanel_ftp' => $this->handleCpanelFtp($client, 'delete'),
            'wordpress_user' => $this->handleWordPressUser($client, 'delete'),
            default => Log::warning('Unknown account type', ['type' => $account->account_type]),
        };
    }

    protected function handleCpanelEmail($client, string $operation): void
    {
        $account = $this->linkedAccount;
        $domain = 'example.com';

        $result = match ($operation) {
            'create' => $client->createEmailAccount(
                $domain,
                $account->external_username,
                'temp_password',
                0
            ),
            'update' => $account->desired_state === 'suspended'
                ? $client->suspendEmailAccount($domain, $account->external_username)
                : $client->unsuspendEmailAccount($domain, $account->external_username),
            'delete' => $client->deleteEmailAccount($domain, $account->external_username),
            default => null,
        };

        Log::info('cPanel Email operation completed', [
            'operation' => $operation,
            'result' => $result,
        ]);
    }

    protected function handleCpanelFtp($client, string $operation): void
    {
        $account = $this->linkedAccount;
        $domain = 'example.com';

        $result = match ($operation) {
            'create' => $client->createFtpAccount(
                $domain,
                $account->external_username,
                'temp_password'
            ),
            'update' => $result = null,
            'delete' => $client->deleteFtpAccount($domain, $account->external_username),
            default => null,
        };

        Log::info('cPanel FTP operation completed', [
            'operation' => $operation,
            'result' => $result,
        ]);
    }

    protected function handleWordPressUser($client, string $operation): void
    {
        $account = $this->linkedAccount;

        $result = match ($operation) {
            'create' => $client->createUser([
                'username' => $account->external_username,
                'email' => $account->external_email,
                'first_name' => $account->user?->first_name,
                'last_name' => $account->user?->last_name,
                'roles' => [$account->external_role ?? 'subscriber'],
            ]),
            'update' => $client->updateUser($account->external_id, [
                'roles' => [$account->external_role ?? 'subscriber'],
            ]),
            'delete' => $client->deleteUser($account->external_id),
            default => null,
        };

        Log::info('WordPress User operation completed', [
            'operation' => $operation,
            'result' => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncLinkedAccountData job failed permanently', [
            'linked_account_id' => $this->linkedAccount->id,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);

        $this->linkedAccount->update([
            'last_synced_at' => now(),
            'last_sync_status' => 'failed',
            'provisioning_status' => 'failed',
        ]);
    }
}
