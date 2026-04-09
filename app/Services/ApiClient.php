<?php

namespace App\Services;

use App\Models\Connector;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

abstract class ApiClient
{
    protected Client $client;
    protected Connector $connector;
    protected array $config;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
        
        $baseUrl = rtrim($connector->base_url, '/') . '/';
        
        $authHeader = 'cpanel ' . $connector->username . ':' . $connector->secret;
        
        $this->config = [
            'base_uri' => $baseUrl,
            'timeout' => $connector->timeout_seconds ?? 30,
            'verify' => $connector->ssl_verify ?? true,
            'headers' => [
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ],
        ];
    }

    protected function getClient(): Client
    {
        if (!isset($this->client)) {
            $this->client = new Client($this->config);
        }
        return $this->client;
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->getClient()->request($method, $uri, $options);
            return [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ];
        } catch (RequestException $e) {
            Log::error('API Request Failed', [
                'connector' => $this->connector->name,
                'method' => $method,
                'uri' => $uri,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function get(string $uri, array $options = []): array
    {
        return $this->request('GET', $uri, $options);
    }

    protected function post(string $uri, array $options = []): array
    {
        return $this->request('POST', $uri, $options);
    }

    protected function put(string $uri, array $options = []): array
    {
        return $this->request('PUT', $uri, $options);
    }

    protected function delete(string $uri, array $options = []): array
    {
        return $this->request('DELETE', $uri, $options);
    }

    abstract public function testConnection(): bool;
}
