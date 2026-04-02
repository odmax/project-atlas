<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($setting) {
            if (empty($setting->key)) {
                $setting->key = Str::slug($setting->key);
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
