<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $guarded = [];

    /**
     * Get a setting value by key with cache support.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return $default;
            }

            return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();

                return $setting?->value ?? $default;
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Set a setting key-value pair and clear cache.
     */
    public static function set(string $key, ?string $value): self
    {
        Cache::forget("app_setting_{$key}");

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get the website / application name.
     */
    public static function getSiteName(): string
    {
        return (string) static::get('site_name', config('app.name', 'WABlast'));
    }

    /**
     * Get logo URL or null.
     */
    public static function getLogoUrl(): ?string
    {
        $path = static::get('logo_path');
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Get favicon URL or default SVG data URI.
     */
    public static function getFaviconUrl(): string
    {
        $path = static::get('favicon_path');
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Default WhatsApp-green favicon SVG data URI
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23128C7E'%3E%3Cpath d='M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.383-.058c.104-.13.454-.53.577-.713.123-.183.243-.153.401-.094.159.058 1.006.474 1.179.561.174.087.291.13.334.202.043.073.043.423-.101.827z'/%3E%3C/svg%3E";
    }
}
