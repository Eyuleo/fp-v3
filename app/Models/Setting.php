<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Get a setting value by key with type casting
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type'  => $type,
            ]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Cast value based on type
     */
    protected static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    /**
     * Get the casted value attribute
     */
    public function getCastedValueAttribute(): mixed
    {
        return self::castValue($this->value, $this->type);
    }
}
