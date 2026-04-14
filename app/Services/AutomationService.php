<?php

namespace App\Services;

use App\Jobs\ProcessAutomationAction;
use App\Models\AutomationRule;
use App\Models\AutomationRuleRun;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    public function trigger(string $triggerType, array $payload): void
    {
        $rules = AutomationRule::active()
            ->byTrigger($triggerType)
            ->get();

        foreach ($rules as $rule) {
            $this->executeRule($rule, $payload);
        }
    }

    public function executeRule(AutomationRule $rule, array $payload): AutomationRuleRun
    {
        $run = AutomationRuleRun::create([
            'automation_rule_id' => $rule->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            if (!$this->evaluateConditions($rule, $payload)) {
                $run->markAsCompleted([
                    'skipped' => true,
                    'reason' => 'Conditions not met',
                ]);
                return $run;
            }

            $this->executeActions($rule, $payload);

            $rule->update(['last_run_at' => now()]);
            $run->markAsCompleted([
                'executed' => true,
            ]);

        } catch (\Exception $e) {
            Log::error("Automation rule failed: " . $e->getMessage(), [
                'rule_id' => $rule->id,
                'run_id' => $run->id,
            ]);
            $run->markAsFailed($e->getMessage(), $payload);
        }

        return $run;
    }

    protected function evaluateConditions(AutomationRule $rule, array $payload): bool
    {
        $conditions = $rule->condition_json;

        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'eq';
            $value = $condition['value'] ?? null;

            if (!$field) {
                continue;
            }

            $payloadValue = data_get($payload, $field);

            if (!$this->evaluateOperator($payloadValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateOperator($actual, string $operator, $expected): bool
    {
        return match ($operator) {
            'eq' => $actual == $expected,
            'neq' => $actual != $expected,
            'gt' => $actual > $expected,
            'gte' => $actual >= $expected,
            'lt' => $actual < $expected,
            'lte' => $actual <= $expected,
            'contains' => is_string($actual) && str_contains($actual, $expected),
            'in' => is_array($expected) && in_array($actual, $expected),
            'exists' => $actual !== null,
            'not_exists' => $actual === null,
            default => true,
        };
    }

    protected function executeActions(AutomationRule $rule, array $payload): void
    {
        $actions = $rule->action_json;

        if (empty($actions)) {
            return;
        }

        foreach ($actions as $action) {
            $actionType = $action['type'] ?? null;
            $actionParams = $action['params'] ?? [];

            $this->executeAction($actionType, $actionParams, $payload);
        }
    }

    protected function executeAction(string $type, array $params, array $payload): void
    {
        $result = match ($type) {
            'create_audit_log' => $this->actionCreateAuditLog($params, $payload),
            'mark_for_review' => $this->actionMarkForReview($params, $payload),
            'queue_retry' => $this->actionQueueRetry($params, $payload),
            'set_user_status' => $this->actionSetUserStatus($params, $payload),
            default => Log::warning("Unknown automation action: {$type}"),
        };
    }

    protected function actionCreateAuditLog(array $params, array $payload): void
    {
        Log::info("Automation: create_audit_log", [
            'params' => $params,
            'payload' => $payload,
        ]);
    }

    protected function actionMarkForReview(array $params, array $payload): void
    {
        Log::info("Automation: mark_for_review", [
            'params' => $params,
            'payload' => $payload,
        ]);
    }

    protected function actionQueueRetry(array $params, array $payload): void
    {
        $jobClass = $params['job_class'] ?? null;
        $jobId = $payload['job_id'] ?? null;

        if ($jobClass && $jobId) {
            Log::info("Automation: queue_retry for job {$jobClass}", [
                'job_id' => $jobId,
            ]);
        }
    }

    protected function actionSetUserStatus(array $params, array $payload): void
    {
        $userId = $payload['user_id'] ?? $payload['userId'] ?? null;
        $status = $params['status'] ?? null;

        if ($userId && $status) {
            User::where('id', $userId)->update(['lifecycle_status' => $status]);
            Log::info("Automation: set_user_status to {$status}", ['user_id' => $userId]);
        }
    }
}
