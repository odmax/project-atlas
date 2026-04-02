<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$connector = App\Models\Connector::firstOrCreate(
    ['name' => 'Test cPanel'],
    [
        'uuid' => \Str::uuid(),
        'type' => 'cpanel',
        'base_url' => 'https://test.cpanel.net:2083',
        'username' => 'testuser',
        'secret' => 'testpassword',
        'is_active' => true,
        'ssl_verify' => false,
        'timeout_seconds' => 30,
    ]
);

echo "Created/Found Connector ID: " . $connector->id . "\n";
echo "Name: " . $connector->name . "\n";
echo "Type: " . $connector->type . "\n";
echo "Base URL: " . $connector->base_url . "\n";
echo "Active: " . ($connector->is_active ? 'Yes' : 'No') . "\n";
