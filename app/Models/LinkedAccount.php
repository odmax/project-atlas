<?php

namespace App\Models;

use App\Jobs\SyncLinkedAccountData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LinkedAccount extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'connector_id',
        'account_type',
        'external_id',
        'external_username',
        'external_email',
        'desired_state',
        'actual_state',
        'is_suspended',
        'provisioning_status',
        'external_role',
        'last_synced_at',
        'last_sync_status',
        'metadata_json',
        'domain',
    ];

    protected function casts(): array
    {
        return [
            'is_suspended' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($linkedAccount) {
            if (empty($linkedAccount->uuid)) {
                $linkedAccount->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($linkedAccount) {
            SyncLinkedAccountData::dispatch($linkedAccount, 'create');
        });

        static::updated(function ($linkedAccount) {
            if ($linkedAccount->wasChanged(['desired_state', 'actual_state', 'is_suspended'])) {
                SyncLinkedAccountData::dispatch($linkedAccount, 'update');
            }
        });

        static::deleted(function ($linkedAccount) {
            SyncLinkedAccountData::dispatch($linkedAccount, 'delete');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function connector()
    {
        return $this->belongsTo(Connector::class);
    }
}
