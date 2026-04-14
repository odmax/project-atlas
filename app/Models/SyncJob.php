<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SyncJob extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_QUEUED_FOR_RETRY = 'queued_for_retry';

    protected $fillable = [
        'uuid',
        'user_id',
        'connector_id',
        'linked_account_id',
        'service_assignment_id',
        'job_type',
        'status',
        'direction',
        'correlation_id',
        'started_at',
        'completed_at',
        'last_error',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->uuid)) {
                $job->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedAccount::class);
    }

    public function serviceAssignment(): BelongsTo
    {
        return $this->belongsTo(ServiceAssignment::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SyncJobAttempt::class, 'sync_job_id')->orderBy('attempt_number');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isQueuedForRetry(): bool
    {
        return $this->status === self::STATUS_QUEUED_FOR_RETRY;
    }

    public function canRetry(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_QUEUED_FOR_RETRY]);
    }

    public function markAsRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'last_error' => $error,
        ]);
    }

    public function markAsQueuedForRetry(): void
    {
        $this->update([
            'status' => self::STATUS_QUEUED_FOR_RETRY,
        ]);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    public function scopeQueuedForRetry($query)
    {
        return $query->where('status', self::STATUS_QUEUED_FOR_RETRY);
    }
}
