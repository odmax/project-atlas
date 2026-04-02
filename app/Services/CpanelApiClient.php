<?php

namespace App\Services;

use App\Models\Connector;
use Illuminate\Support\Facades\Log;

class CpanelApiClient extends ApiClient
{
    public function __construct(Connector $connector)
    {
        if ($connector->type !== 'cpanel') {
            throw new \InvalidArgumentException('Connector must be of type cpanel');
        }
        parent::__construct($connector);
    }

    public function testConnection(): bool
    {
        try {
            $result = $this->get('json-api/cpanel?api2_json=1&func=loadavg');
            return isset($result['status']) && $result['status'] === 200;
        } catch (\Exception $e) {
            Log::error('cPanel Connection Test Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function listEmailAccounts(string $domain): array
    {
        $result = $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'list_pops',
                'domain' => $domain,
            ],
        ]);
        return $result['body'] ?? [];
    }

    public function createEmailAccount(string $domain, string $email, string $password, int $quota = 0): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'add_pop',
                'domain' => $domain,
                'email' => $email,
                'password' => $password,
                'quota' => $quota,
            ],
        ]);
    }

    public function deleteEmailAccount(string $domain, string $email): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'delete_pop',
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function suspendEmailAccount(string $domain, string $email): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'suspend_pop',
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function unsuspendEmailAccount(string $domain, string $email): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'unsuspend_pop',
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function listFtpAccounts(string $domain): array
    {
        $result = $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'list_ftp',
                'domain' => $domain,
            ],
        ]);
        return $result['body'] ?? [];
    }

    public function createFtpAccount(string $domain, string $username, string $password, string $homedir = ''): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'add_ftp',
                'domain' => $domain,
                'user' => $username,
                'pass' => $password,
                'homedir' => $homedir,
            ],
        ]);
    }

    public function deleteFtpAccount(string $domain, string $username): array
    {
        return $this->get('json-api/cpanel', [
            'query' => [
                'api2_json' => 1,
                'func' => 'delete_ftp',
                'domain' => $domain,
                'user' => $username,
            ],
        ]);
    }
}
