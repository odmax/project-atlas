<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('primary_email', 'admin@atlas.test')->first();

if ($user) {
    $user->assignRole('super_admin');
    echo "Assigned super_admin role to: " . $user->primary_email . "\n";
} else {
    echo "User not found\n";
}
