<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settings = [
    'platform.app_name' => 'Atlas',
    'platform.app_url' => 'http://localhost:8000',
    'platform.timezone' => 'UTC',
    'sync.interval_minutes' => '5',
    'sync.auto_sync' => '1',
    'sync.max_retries' => '3',
    'notifications.email_on_failure' => '1',
    'notifications.email_on_success' => '0',
    'notifications.email_recipients' => '',
    'connector.default_cpanel_domain' => '',
    'connector.default_timeout' => '30',
    'connector.default_ssl_verify' => '1',
    'admin.name' => 'Admin',
    'admin.email' => 'admin@atlas.test',
    'admin.phone' => '',
    'admin.force_password_change' => '0',
    'admin.session_timeout' => '60',
    'admin.default_theme' => 'system',
    'admin.default_language' => 'en',
];

foreach ($settings as $key => $value) {
    App\Models\Setting::updateOrCreate(
        ['key' => $key],
        ['value' => $value]
    );
}

echo "Default settings created!\n";
echo "Total settings: " . App\Models\Setting::count() . "\n";
