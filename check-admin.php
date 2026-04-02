<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('primary_email', 'admin@atlas.test')->first();

if ($user) {
    echo "Admin user exists!\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->first_name . " " . $user->last_name . "\n";
    echo "Email: " . $user->primary_email . "\n";
} else {
    echo "No admin user found.\n";
}
