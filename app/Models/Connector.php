<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Connector extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'type',
        'base_url',
        'username',
        'secret',
        'is_active',
        'ssl_verify',
        'timeout_seconds',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ssl_verify' => 'boolean',
            'timeout_seconds' => 'integer',
            'secret' => 'encrypted',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($connector) {
            if (empty($connector->uuid)) {
                $connector->uuid = (string) Str::uuid();
            }
        });
    }

    public function linkedAccounts()
    {
        return $this->hasMany(LinkedAccount::class);
    }
}
