<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            // Suspension rules
            [
                'key' => 'suspension.cascade_to_linked_accounts',
                'category' => 'suspension',
                'value_json' => true,
                'description' => 'When user is suspended, cascade suspension to all linked accounts',
            ],
            [
                'key' => 'suspension.auto_provision_on_reactivate',
                'category' => 'suspension',
                'value_json' => true,
                'description' => 'Automatically reprovision services when user is reactivated',
            ],

            // WordPress policies
            [
                'key' => 'wordpress.suspend.rotate_password',
                'category' => 'wordpress',
                'value_json' => true,
                'description' => 'Rotate WordPress password on suspension',
            ],
            [
                'key' => 'wordpress.suspend.revoke_application_passwords',
                'category' => 'wordpress',
                'value_json' => true,
                'description' => 'Revoke all application passwords on suspension',
            ],
            [
                'key' => 'wordpress.suspend.downgrade_role',
                'category' => 'wordpress',
                'value_json' => true,
                'description' => 'Downgrade WordPress role on suspension',
            ],
            [
                'key' => 'wordpress.suspend.default_role',
                'category' => 'wordpress',
                'value_json' => 'subscriber',
                'description' => 'Default role to assign when WordPress account is suspended',
            ],
            [
                'key' => 'wordpress.provision.default_role',
                'category' => 'wordpress',
                'value_json' => 'contributor',
                'description' => 'Default role for new WordPress users',
            ],

            // cPanel email policies
            [
                'key' => 'cpanel.email.suspend_login',
                'category' => 'cpanel',
                'value_json' => true,
                'description' => 'Suspend cPanel login on user suspension',
            ],
            [
                'key' => 'cpanel.email.suspend_incoming',
                'category' => 'cpanel',
                'value_json' => false,
                'description' => 'Suspend incoming email on suspension',
            ],
            [
                'key' => 'cpanel.email.suspend_outgoing',
                'category' => 'cpanel',
                'value_json' => false,
                'description' => 'Suspend outgoing email on suspension',
            ],

            // Job policies
            [
                'key' => 'jobs.retry.max_attempts',
                'category' => 'jobs',
                'value_json' => 3,
                'description' => 'Maximum retry attempts for failed jobs',
            ],
            [
                'key' => 'jobs.retry.backoff_multiplier',
                'category' => 'jobs',
                'value_json' => 2,
                'description' => 'Exponential backoff multiplier for job retries',
            ],
            [
                'key' => 'jobs.retry.max_delay_seconds',
                'category' => 'jobs',
                'value_json' => 3600,
                'description' => 'Maximum delay between job retries in seconds',
            ],

            // Reconciliation policies
            [
                'key' => 'reconciliation.auto_remediate.warning',
                'category' => 'reconciliation',
                'value_json' => false,
                'description' => 'Auto-remediate warnings during reconciliation',
            ],
            [
                'key' => 'reconciliation.frequency_hours',
                'category' => 'reconciliation',
                'value_json' => 24,
                'description' => 'How often to run reconciliation checks (hours)',
            ],
            [
                'key' => 'reconciliation.tolerance_minutes',
                'category' => 'reconciliation',
                'value_json' => 15,
                'description' => 'Tolerance window for reconciliation (minutes)',
            ],

            // Linked Account policies
            [
                'key' => 'linked_accounts.auto_sync_on_create',
                'category' => 'linked_accounts',
                'value_json' => true,
                'description' => 'Auto-sync linked account after creation',
            ],
            [
                'key' => 'linked_accounts.sync_timeout_seconds',
                'category' => 'linked_accounts',
                'value_json' => 300,
                'description' => 'Timeout for linked account sync operations',
            ],
        ];

        foreach ($policies as $policy) {
            Policy::updateOrCreate(
                ['key' => $policy['key']],
                $policy
            );
        }

        $this->command->info('Seeded ' . count($policies) . ' policies.');
    }
}
