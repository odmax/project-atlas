<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MigrationItem extends Model
{
    protected $fillable = [
        'uuid',
        'migration_batch_id',
        'user_id',
        'connector_id',
        'linked_account_id',
        'action',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'retry_count',
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

        static::creating(function ($migrationItem) {
            if (empty($migrationItem->uuid)) {
                $migrationItem->uuid = (string) Str::uuid();
            }
        });
    }
}
