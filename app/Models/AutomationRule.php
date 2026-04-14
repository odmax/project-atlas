<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AutomationRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'trigger_type',
        'condition_json',
        'action_json',
        'is_active',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'condition_json' => 'array',
            'action_json' => 'array',
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($rule) {
            if (empty($rule->uuid)) {
                $rule->uuid = (string) Str::uuid();
            }
        });
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRuleRun::class, 'automation_rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger_type', $trigger);
    }

    public function isDueForRun(): bool
    {
        if (!$this->last_run_at) {
            return true;
        }

        return now()->diffInMinutes($this->last_run_at) >= 5;
    }
}
