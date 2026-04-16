<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\LinkedAccount;
use App\Models\ServiceAssignment;
use App\Models\SyncJob;
use App\Models\SyncJobAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SystemWiringService
{
    protected AutomationService $automationService;
    protected SyncJobService $syncJobService;

    public function __construct(
        AutomationService $automationService,
        SyncJobService $syncJobService
    ) {
        $this->automationService = $automationService;
        $this->syncJobService = $syncJobService;
    }

    public function triggerUserCreated(User $user): void
    {
        $this->automationService->trigger('user.created', [
            'user_id' => $user->id,
            'user_uuid' => $user->uuid,
            'user_email' => $user->primary_email,
            'department' => $user->department,
            'employment_status' => $user->employment_status,
        ]);

        Log::info("System wiring: user.created triggered", ['user_id' => $user->id]);
    }

    public function triggerUserSuspended(User $user): void
    {
        $this->automationService->trigger('user.suspended', [
            'user_id' => $user->id,
            'user_uuid' => $user->uuid,
            'user_email' => $user->primary_email,
            'employment_status' => $user->employment_status,
        ]);

        $this->startDeprovisioningForUser($user, 'suspended');

        Log::info("System wiring: user.suspended triggered", ['user_id' => $user->id]);
    }

    public function triggerUserUnsuspended(User $user): void
    {
        $this->automationService->trigger('user.unsuspended', [
            'user_id' => $user->id,
            'user_uuid' => $user->uuid,
            'user_email' => $user->primary_email,
            'employment_status' => $user->employment_status,
        ]);

        $this->startProvisioningForUser($user, 'unsuspended');

        Log::info("System wiring: user.unsuspended triggered", ['user_id' => $user->id]);
    }

    public function triggerJobFailed(SyncJob $job): void
    {
        $this->createJobAttempt($job, 'failed');

        $this->automationService->trigger('job.failed', [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_type' => $job->job_type,
            'connector_id' => $job->connector_id,
            'user_id' => $job->user_id,
            'service_assignment_id' => $job->service_assignment_id,
            'error' => $job->last_error,
        ]);

        Log::info("System wiring: job.failed triggered", ['job_id' => $job->id]);
    }

    public function triggerJobCompleted(SyncJob $job): void
    {
        $this->automationService->trigger('job.completed', [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_type' => $job->job_type,
            'connector_id' => $job->connector_id,
            'user_id' => $job->user_id,
            'service_assignment_id' => $job->service_assignment_id,
            'linked_account_id' => $job->linked_account_id,
        ]);

        if ($job->job_type === 'provision' && $job->service_assignment_id) {
            $this->onProvisioningCompleted($job);
        }

        Log::info("System wiring: job.completed triggered", ['job_id' => $job->id]);
    }

    public function startProvisioning(ServiceAssignment $assignment): SyncJob
    {
        $job = SyncJob::create([
            'user_id' => $assignment->user_id,
            'connector_id' => $assignment->connector_id,
            'service_assignment_id' => $assignment->id,
            'job_type' => 'provision',
            'status' => SyncJob::STATUS_PENDING,
            'direction' => 'inbound',
            'metadata_json' => [
                'account_type' => $assignment->account_type,
                'default_role' => $assignment->default_role,
                'template_id' => $assignment->service_template_id,
            ],
        ]);

        $assignment->update(['status' => 'provisioning']);

        Log::info("System wiring: provisioning started", [
            'service_assignment_id' => $assignment->id,
            'sync_job_id' => $job->id,
        ]);

        return $job;
    }

    public function startDeprovisioning(ServiceAssignment $assignment): SyncJob
    {
        $job = SyncJob::create([
            'user_id' => $assignment->user_id,
            'connector_id' => $assignment->connector_id,
            'service_assignment_id' => $assignment->id,
            'job_type' => 'deprovision',
            'status' => SyncJob::STATUS_PENDING,
            'direction' => 'outbound',
            'metadata_json' => [
                'account_type' => $assignment->account_type,
            ],
        ]);

        $assignment->update(['status' => 'deprovisioning']);

        Log::info("System wiring: deprovisioning started", [
            'service_assignment_id' => $assignment->id,
            'sync_job_id' => $job->id,
        ]);

        return $job;
    }

    protected function startDeprovisioningForUser(User $user, string $trigger): void
    {
        $assignments = ServiceAssignment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        foreach ($assignments as $assignment) {
            $this->startDeprovisioning($assignment);
        }
    }

    protected function startProvisioningForUser(User $user, string $trigger): void
    {
        $assignments = ServiceAssignment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'deprovisioned'])
            ->get();

        foreach ($assignments as $assignment) {
            $this->startProvisioning($assignment);
        }
    }

    protected function createJobAttempt(SyncJob $job, string $status): SyncJobAttempt
    {
        $attemptNumber = $job->attempts()->max('attempt_number') + 1;

        return SyncJobAttempt::create([
            'sync_job_id' => $job->id,
            'attempt_number' => $attemptNumber,
            'status' => $status,
            'error' => $job->last_error,
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at,
        ]);
    }

    protected function onProvisioningCompleted(SyncJob $job): void
    {
        $assignment = $job->serviceAssignment;

        if (!$assignment) {
            return;
        }

        if ($job->linked_account_id) {
            $assignment->update([
                'status' => 'active',
            ]);
        } else {
            $assignment->update([
                'status' => 'failed',
            ]);
        }

        Log::info("System wiring: provisioning completed", [
            'job_id' => $job->id,
            'assignment_id' => $assignment->id,
            'status' => $assignment->status,
        ]);
    }

    public function onProvisioningSuccess(SyncJob $job, LinkedAccount $linkedAccount): void
    {
        $job->update([
            'linked_account_id' => $linkedAccount->id,
            'status' => SyncJob::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        if ($job->service_assignment_id) {
            ServiceAssignment::where('id', $job->service_assignment_id)->update([
                'status' => 'active',
            ]);
        }

        $this->triggerJobCompleted($job);
    }

    public function onProvisioningFailure(SyncJob $job, string $error): void
    {
        $this->createJobAttempt($job, 'failed');

        $job->update([
            'status' => SyncJob::STATUS_FAILED,
            'completed_at' => now(),
            'last_error' => $error,
        ]);

        if ($job->service_assignment_id) {
            ServiceAssignment::where('id', $job->service_assignment_id)->update([
                'status' => 'failed',
            ]);
        }

        $this->triggerJobFailed($job);
    }
}