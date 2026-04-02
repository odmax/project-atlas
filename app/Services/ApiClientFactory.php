<?php

namespace App\Services;

use App\Models\Connector;
use InvalidArgumentException;

class ApiClientFactory
{
    public static function create(Connector $connector): ApiClient
    {
        return match ($connector->type) {
            'cpanel' => new CpanelApiClient($connector),
            'wordpress' => new WordPressApiClient($connector),
            default => throw new InvalidArgumentException("Unsupported connector type: {$connector->type}"),
        };
    }

    public static function testConnection(Connector $connector): bool
    {
        try {
            $client = self::create($connector);
            return $client->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }
}
