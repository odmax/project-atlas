<?php

namespace App\Services;

use App\Models\LinkedAccount;
use App\Models\SyncJob;
use App\Models\SyncJobAttempt;
use Illuminate\Support\Str;

class SyncJobService
{
    protected ?SystemWiringService $wiringService = null;

    protected function getWiringService(): SystemWiringService
    {
        if (!$this->wiringService) {
            $this->wiringService = app(SystemWiringService::class);
        }
        return $this->wiringService;
    }

    public function createJob(
        string $jobType,
        ?int $userId = null,
        ?int $connectorId = null,
        ?int $linkedAccountId = null,
        ?int $serviceAssignmentId = null,
        ?string $direction = null,
        array $metadata = []
    ): SyncJob {
        return SyncJob::create([
            'job_type' => $jobType,
            'user_id' => $userId,
            'connector_id' => $connectorId,
            'linked_account_id' => $linkedAccountId,
            'service_assignment_id' => $serviceAssignmentId,
            'direction' => $direction,
            'correlation_id' => Str::uuid()->toString(),
            'status' => SyncJob::STATUS_PENDING,
            'metadata_json' => $metadata,
        ]);
    }

    public function startJob(SyncJob $job): SyncJob
    {
        $job->markAsRunning();
        return $job->fresh();
    }

    public function completeJob(SyncJob $job, array $metadata = []): SyncJob
    {
        $job->update([
            'metadata_json' => array_merge($job->metadata_json ?? [], $metadata),
        ]);
        $job->markAsCompleted();
        
        $this->getWiringService()->triggerJobCompleted($job);
        
        return $job->fresh();
    }

    public function completeJobWithLinkedAccount(SyncJob $job, LinkedAccount $linkedAccount, array $metadata = []): SyncJob
    {
        $job->update([
            'linked_account_id' => $linkedAccount->id,
            'metadata_json' => array_merge($job->metadata_json ?? [], $metadata),
        ]);
        $job->markAsCompleted();
        
        $this->getWiringService()->onProvisioningSuccess($job, $linkedAccount);
        
        return $job->fresh();
    }

    public function failJob(SyncJob $job, string $error, array $metadata = []): SyncJob
    {
        $job->update([
            'metadata_json' => array_merge($job->metadata_json ?? [], $metadata),
            'last_error' => $error,
        ]);
        $job->markAsFailed($error);
        
        $this->getWiringService()->triggerJobFailed($job);
        
        return $job->fresh();
    }

    public function failJobWithAssignment(SyncJob $job, string $error, array $metadata = []): SyncJob
    {
        $job->update([
            'metadata_json' => array_merge($job->metadata_json ?? [], $metadata),
            'last_error' => $error,
        ]);
        $job->markAsFailed($error);
        
        $this->getWiringService()->onProvisioningFailure($job, $error);
        
        return $job->fresh();
    }

    public function queueForRetry(SyncJob $job): SyncJob
    {
        $job->markAsQueuedForRetry();
        return $job->fresh();
    }

    public function startAttempt(SyncJob $job, int $attemptNumber): SyncJobAttempt
    {
        return SyncJobAttempt::create([
            'sync_job_id' => $job->id,
            'attempt_number' => $attemptNumber,
            'status' => SyncJobAttempt::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    public function completeAttempt(SyncJobAttempt $attempt, array $response = []): SyncJobAttempt
    {
        $attempt->markAsCompleted($response);
        return $attempt->fresh();
    }

    public function failAttempt(SyncJobAttempt $attempt, string $error): SyncJobAttempt
    {
        $attempt->markAsFailed($error);
        return $attempt->fresh();
    }

    public function getJobStats(): array
    {
        return [
            'pending' => SyncJob::where('status', SyncJob::STATUS_PENDING)->count(),
            'running' => SyncJob::where('status', SyncJob::STATUS_RUNNING)->count(),
            'completed' => SyncJob::where('status', SyncJob::STATUS_COMPLETED)->count(),
            'failed' => SyncJob::where('status', SyncJob::STATUS_FAILED)->count(),
            'queued_for_retry' => SyncJob::where('status', SyncJob::STATUS_QUEUED_FOR_RETRY)->count(),
        ];
    }

    public function getRecentFailedJobs(int $limit = 10)
    {
        return SyncJob::failed()
            ->with(['connector', 'user'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
