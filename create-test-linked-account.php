<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$linkedAccount = App\Models\LinkedAccount::firstOrCreate(
    [
        'user_id' => 1,
        'connector_id' => 3,
        'external_username' => 'testemail',
    ],
    [
        'uuid' => \Str::uuid(),
        'account_type' => 'cpanel_email',
        'external_email' => 'testemail@testdomain.com',
        'external_id' => 'testemail@testdomain.com',
        'desired_state' => 'active',
        'provisioning_status' => 'pending',
    ]
);

echo "Created/Found LinkedAccount ID: " . $linkedAccount->id . "\n";
echo "User ID: " . $linkedAccount->user_id . "\n";
echo "Connector ID: " . $linkedAccount->connector_id . "\n";
echo "Account Type: " . $linkedAccount->account_type . "\n";
echo "Desired State: " . $linkedAccount->desired_state . "\n";
echo "Jobs in queue: " . DB::table('jobs')->count() . "\n";
