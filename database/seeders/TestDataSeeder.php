<?php

namespace Database\Seeders;

use App\Models\Connector;
use App\Models\LinkedAccount;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['primary_email' => 'admin@atlas.test'],
            [
                'uuid' => Str::uuid(),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'display_name' => 'Admin User',
                'password' => bcrypt('password123'),
                'employment_status' => 'active',
                'lifecycle_status' => 'active',
            ]
        );

        $connector = Connector::firstOrCreate(
            ['name' => 'cPanel'],
            [
                'uuid' => Str::uuid(),
                'type' => 'cpanel',
                'base_url' => 'https://cpanel.example.com',
                'username' => 'admin',
                'secret' => 'someSecret',
                'is_active' => true,
                'ssl_verify' => true,
                'timeout_seconds' => 30,
            ]
        );

        LinkedAccount::firstOrCreate(
            [
                'user_id' => $user->id,
                'connector_id' => $connector->id,
                'external_username' => 'user1',
            ],
            [
                'uuid' => Str::uuid(),
                'account_type' => 'cpanel_email',
                'external_id' => 'user@domain.com',
                'external_email' => 'user@domain.com',
                'desired_state' => 'active',
                'actual_state' => 'active',
                'is_suspended' => false,
            ]
        );

        $this->command->info('Test data seeded successfully!');
    }
}
