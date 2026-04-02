<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$la = App\Models\LinkedAccount::create([
    'user_id' => 1,
    'connector_id' => 1,
    'account_type' => 'cpanel_email',
    'external_username' => 'testuser',
    'external_email' => 'test@example.com',
    'desired_state' => 'active',
]);

echo "Created LinkedAccount ID: " . $la->id . "\n";
echo "Jobs in queue: " . DB::table('jobs')->count() . "\n";
