<?php

namespace App\Services;

use App\Models\Connector;
use Illuminate\Support\Facades\Log;

class CpanelApiClient extends ApiClient
{
    protected array $lastError = [];
    
    public function __construct(Connector $connector)
    {
        if ($connector->type !== 'cpanel') {
            throw new \InvalidArgumentException('Connector must be of type cpanel');
        }
        parent::__construct($connector);
    }

    public function testConnection(): bool
    {
        // Try multiple authentication methods
        $methods = [
            'api_token' => $this->testWithApiToken(),
            'basic_auth' => $this->testWithBasicAuth(),
        ];
        
        // Return true if any method succeeded
        foreach ($methods as $method => $result) {
            if ($result) {
                Log::info("cPanel connection successful using method: {$method}");
                return true;
            }
        }
        
        Log::error('cPanel Connection Test Failed - All methods failed', [
            'connector' => $this->connector->name,
            'last_error' => $this->lastError,
        ]);
        
        return false;
    }
    
    protected function testWithApiToken(): bool
    {
        try {
            $result = $this->get('execute/System/loadavg');
            $status = $result['status'] ?? 0;
            $body = $result['body'] ?? [];
            
            if ($status === 200 && isset($body['cpanelresult']['data'])) {
                return true;
            }
            if ($status === 200 && isset($body['loadavg'])) {
                return true;
            }
            
            $this->lastError = ['method' => 'api_token', 'message' => 'API token auth failed'];
            return false;
        } catch (\Exception $e) {
            $this->lastError = ['method' => 'api_token', 'message' => $e->getMessage()];
            return false;
        }
    }
    
    protected function testWithBasicAuth(): bool
    {
        try {
            // Try with basic auth by creating a new client
            $username = $this->connector->username;
            $secret = $this->connector->secret;
            
            $client = new \GuzzleHttp\Client([
                'base_uri' => rtrim($this->connector->base_url, '/') . '/',
                'timeout' => $this->connector->timeout_seconds ?? 30,
                'verify' => $this->connector->ssl_verify ?? true,
                'auth' => [$username, $secret],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            
            $response = $client->get('execute/System/loadavg');
            $status = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            if ($status === 200 && isset($body['cpanelresult']['data'])) {
                return true;
            }
            if ($status === 200 && isset($body['loadavg'])) {
                return true;
            }
            
            $this->lastError = ['method' => 'basic_auth', 'message' => 'Basic auth failed'];
            return false;
        } catch (\Exception $e) {
            $this->lastError = ['method' => 'basic_auth', 'message' => $e->getMessage()];
            return false;
        }
    }
    
    public function getLastError(): array
    {
        return $this->lastError;
    }

    public function listEmailAccounts(string $domain): array
    {
        $result = $this->get('execute/Email/list_pops', [
            'query' => [
                'domain' => $domain,
            ],
        ]);
        return $result['body'] ?? [];
    }

    public function createEmailAccount(string $domain, string $email, string $password, int $quota = 0): array
    {
        return $this->get('execute/Email/add_pop', [
            'query' => [
                'domain' => $domain,
                'email' => $email,
                'password' => $password,
                'quota' => $quota,
            ],
        ]);
    }

    public function deleteEmailAccount(string $domain, string $email): array
    {
        return $this->get('execute/Email/delete_pop', [
            'query' => [
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function suspendEmailAccount(string $domain, string $email): array
    {
        return $this->get('execute/Email/suspend_pop', [
            'query' => [
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function unsuspendEmailAccount(string $domain, string $email): array
    {
        return $this->get('execute/Email/unsuspend_pop', [
            'query' => [
                'domain' => $domain,
                'email' => $email,
            ],
        ]);
    }

    public function listFtpAccounts(string $domain): array
    {
        $result = $this->get('execute/Ftp/list_ftp', [
            'query' => [
                'domain' => $domain,
            ],
        ]);
        return $result['body'] ?? [];
    }

    public function createFtpAccount(string $domain, string $username, string $password, string $homedir = ''): array
    {
        return $this->get('execute/Ftp/add_ftp', [
            'query' => [
                'domain' => $domain,
                'user' => $username,
                'pass' => $password,
                'homedir' => $homedir,
            ],
        ]);
    }

    public function deleteFtpAccount(string $domain, string $username): array
    {
        return $this->get('execute/Ftp/delete_ftp', [
            'query' => [
                'domain' => $domain,
                'user' => $username,
            ],
        ]);
    }
}
