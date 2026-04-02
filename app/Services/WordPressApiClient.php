<?php

namespace App\Services;

use App\Models\Connector;
use Illuminate\Support\Facades\Log;

class WordPressApiClient extends ApiClient
{
    protected string $wpUsername;
    protected string $wpApplicationPassword;

    public function __construct(Connector $connector)
    {
        if ($connector->type !== 'wordpress') {
            throw new \InvalidArgumentException('Connector must be of type wordpress');
        }
        
        $meta = $connector->meta_json ?? [];
        $this->wpUsername = $meta['wp_username'] ?? $connector->username;
        $this->wpApplicationPassword = $meta['wp_application_password'] ?? $connector->secret;
        
        parent::__construct($connector);
    }

    protected function getClient(): \GuzzleHttp\Client
    {
        if (!isset($this->client)) {
            $this->client = new \GuzzleHttp\Client($this->config);
        }
        return $this->client;
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->get('wp-json/wp/v2/users/me');
            return isset($response['status']) && $response['status'] === 200;
        } catch (\Exception $e) {
            Log::error('WordPress Connection Test Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function get(string $uri, array $options = []): array
    {
        $options['auth'] = [$this->wpUsername, $this->wpApplicationPassword];
        return parent::get($uri, $options);
    }

    protected function post(string $uri, array $options = []): array
    {
        $options['auth'] = [$this->wpUsername, $this->wpApplicationPassword];
        return parent::post($uri, $options);
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        $options['auth'] = [$this->wpUsername, $this->wpApplicationPassword];
        return parent::request($method, $uri, $options);
    }

    public function listUsers(int $page = 1, int $perPage = 10): array
    {
        $result = $this->get('wp-json/wp/v2/users', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
        return $result['body'] ?? [];
    }

    public function getUser(int $userId): array
    {
        $result = $this->get("wp-json/wp/v2/users/{$userId}");
        return $result['body'] ?? [];
    }

    public function createUser(array $userData): array
    {
        $result = $this->post('wp-json/wp/v2/users', [
            'json' => $userData,
        ]);
        return $result['body'] ?? [];
    }

    public function updateUser(int $userId, array $userData): array
    {
        $result = $this->post("wp-json/wp/v2/users/{$userId}", [
            'json' => $userData,
        ]);
        return $result['body'] ?? [];
    }

    public function deleteUser(int $userId, bool $reassign = false): array
    {
        $options = [];
        if ($reassign) {
            $options['query'] = ['reassign' => $reassign];
        }
        
        $result = $this->delete("wp-json/wp/v2/users/{$userId}", $options);
        return $result['body'] ?? [];
    }

    public function listRoles(): array
    {
        $result = $this->get('wp-json/wp/v2/users');
        return $result['body'] ?? [];
    }
}
