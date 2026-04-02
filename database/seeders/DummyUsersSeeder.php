<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'display_name' => 'John Doe',
                'primary_email' => 'john.doe@atlas.test',
                'department' => 'IT',
                'job_title' => 'Developer',
                'employment_status' => 'active',
                'lifecycle_status' => 'active',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'display_name' => 'Jane Smith',
                'primary_email' => 'jane.smith@atlas.test',
                'department' => 'HR',
                'job_title' => 'HR Manager',
                'employment_status' => 'active',
                'lifecycle_status' => 'active',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Wilson',
                'display_name' => 'Bob Wilson',
                'primary_email' => 'bob.wilson@atlas.test',
                'department' => 'Sales',
                'job_title' => 'Sales Representative',
                'employment_status' => 'active',
                'lifecycle_status' => 'active',
            ],
            [
                'first_name' => 'Alice',
                'last_name' => 'Brown',
                'display_name' => 'Alice Brown',
                'primary_email' => 'alice.brown@atlas.test',
                'department' => 'Marketing',
                'job_title' => 'Marketing Specialist',
                'employment_status' => 'inactive',
                'lifecycle_status' => 'offboarding',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Davis',
                'display_name' => 'Michael Davis',
                'primary_email' => 'michael.davis@atlas.test',
                'department' => 'Finance',
                'job_title' => 'Accountant',
                'employment_status' => 'active',
                'lifecycle_status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['primary_email' => $userData['primary_email']],
                array_merge($userData, [
                    'uuid' => Str::uuid(),
                    'password' => bcrypt('password123'),
                ])
            );
        }

        $this->command->info('Dummy users seeded successfully!');
    }
}
