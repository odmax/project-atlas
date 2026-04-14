<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AutomationRuleRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'automation_rule_id',
        'status',
        'result_json',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'result_json' => 'array',
            'status' => 'string',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($run) {
            if (empty($run->uuid)) {
                $run->uuid = (string) Str::uuid();
            }
        });
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'result_json' => $result,
        ]);
    }

    public function markAsFailed(string $error, array $context = []): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'result_json' => [
                'error' => $error,
                'context' => $context,
            ],
        ]);
    }
}
