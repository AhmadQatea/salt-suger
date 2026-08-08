<?php

namespace App\Models;

use Database\Factories\RestaurantSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RestaurantSetting extends Model
{
    /** @use HasFactory<RestaurantSettingFactory> */
    use HasFactory;

    public const CACHE_KEY = 'restaurant_settings.current';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'restaurant_name',
        'logo',
        'favicon',
        'description',
        'whatsapp_number',
        'currency',
        'primary_color',
        'secondary_color',
        'accent_color',
        'whatsapp_enabled',
        'whatsapp_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'whatsapp_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Resolve the singleton restaurant settings record.
     */
    public static function current(): self
    {
        return static::query()->firstOrFail();
    }

    /**
     * Cached settings for hot public/admin paths (request + store memoization).
     */
    public static function cached(): self
    {
        return once(function (): self {
            $settings = Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): ?self {
                return static::query()->first();
            });

            return $settings ?? static::makeDefaults();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function makeDefaults(): self
    {
        return new static([
            'restaurant_name' => config('app.name', 'Salt&Suger'),
            'description' => 'وجبتك المفضلة... بطلب أسهل وأسرع',
            'currency' => 'ل.س',
            'primary_color' => '#ba0013',
            'secondary_color' => '#111111',
            'accent_color' => '#cca800',
            'whatsapp_enabled' => false,
            'whatsapp_number' => null,
        ]);
    }
}
