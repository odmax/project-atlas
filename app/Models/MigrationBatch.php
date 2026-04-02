<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MigrationBatch extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'status',
        'total_items',
        'processed_items',
        'failed_items',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($migrationBatch) {
            if (empty($migrationBatch->uuid)) {
                $migrationBatch->uuid = (string) Str::uuid();
            }
        });
    }
}
