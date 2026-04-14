<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Policy extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'key',
        'category',
        'value_json',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($policy) {
            if (empty($policy->uuid)) {
                $policy->uuid = (string) Str::uuid();
            }
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $policy = static::where('key', $key)->where('is_active', true)->first();
        
        if (!$policy) {
            return $default;
        }

        return $policy->value_json;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::getValue($key, $default);
        return is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::getValue($key, $default);
        return is_int($value) ? $value : (int) $value;
    }

    public static function getString(string $key, string $default = ''): string
    {
        $value = static::getValue($key, $default);
        return is_string($value) ? $value : (string) $value;
    }

    public static function isActive(string $key): bool
    {
        return static::where('key', $key)->where('is_active', true)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
