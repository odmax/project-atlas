<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::create([
    'uuid' => (string) \Str::uuid(),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'display_name' => 'Admin User',
    'primary_email' => 'admin@atlas.test',
    'password' => \Hash::make('password123'),
    'employment_status' => 'active',
    'lifecycle_status' => 'active',
]);

echo "Admin user created! ID: " . $user->id . "\n";
