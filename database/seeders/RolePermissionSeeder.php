<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            // Connectors
            'connectors.view',
            'connectors.create',
            'connectors.edit',
            'connectors.delete',
            // Linked Accounts
            'linked_accounts.view',
            'linked_accounts.create',
            'linked_accounts.edit',
            'linked_accounts.delete',
            // Migration Batches
            'migration_batches.view',
            'migration_batches.create',
            'migration_batches.edit',
            'migration_batches.delete',
            // Migration Items
            'migration_items.view',
            'migration_items.create',
            'migration_items.edit',
            'migration_items.delete',
            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'connectors.view',
            'connectors.create',
            'connectors.edit',
            'linked_accounts.view',
            'linked_accounts.create',
            'linked_accounts.edit',
            'linked_accounts.delete',
            'migration_batches.view',
            'migration_items.view',
        ]);

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->givePermissionTo([
            'connectors.view',
            'linked_accounts.view',
            'linked_accounts.create',
            'linked_accounts.edit',
            'migration_batches.view',
            'migration_items.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'users.view',
            'connectors.view',
            'linked_accounts.view',
            'migration_batches.view',
            'migration_items.view',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
