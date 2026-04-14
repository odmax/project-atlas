<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use Illuminate\Database\Seeder;

class AutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // User created triggers
            [
                'name' => 'Log New User Creation',
                'trigger_type' => 'user.created',
                'condition_json' => [],
                'action_json' => [
                    ['type' => 'create_audit_log', 'params' => ['message' => 'New user created via onboarding']],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Auto-provision on User Created',
                'trigger_type' => 'user.created',
                'condition_json' => [
                    ['field' => 'lifecycle_status', 'operator' => 'eq', 'value' => 'onboarding'],
                ],
                'action_json' => [
                    ['type' => 'create_audit_log', 'params' => ['message' => 'User requires provisioning']],
                ],
                'is_active' => true,
            ],

            // Reconciliation triggers
            [
                'name' => 'Alert on Reconciliation Drift',
                'trigger_type' => 'reconciliation.drift_detected',
                'condition_json' => [
                    ['field' => 'severity', 'operator' => 'in', 'value' => ['high', 'critical']],
                ],
                'action_json' => [
                    ['type' => 'mark_for_review', 'params' => ['priority' => 'high']],
                ],
                'is_active' => true,
            ],

            // Job failure triggers
            [
                'name' => 'Retry Failed Sync Jobs',
                'trigger_type' => 'job.failed',
                'condition_json' => [
                    ['field' => 'attempts', 'operator' => 'lt', 'value' => 3],
                ],
                'action_json' => [
                    ['type' => 'queue_retry', 'params' => ['job_class' => 'SyncLinkedAccountData']],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Mark User Review on Job Failure',
                'trigger_type' => 'job.failed',
                'condition_json' => [
                    ['field' => 'attempts', 'operator' => 'gte', 'value' => 3],
                ],
                'action_json' => [
                    ['type' => 'mark_for_review', 'params' => ['priority' => 'critical']],
                    ['type' => 'set_user_status', 'params' => ['status' => 'onboarding']],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            AutomationRule::updateOrCreate(
                ['trigger_type' => $rule['trigger_type'], 'name' => $rule['name']],
                $rule
            );
        }

        $this->command->info('Seeded ' . count($rules) . ' automation rules.');
    }
}
