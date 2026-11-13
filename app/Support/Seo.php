<?php

namespace App\Support;

class Seo
{
    public static function set(array $data): void
    {
        view()->share('seo', $data);
    }

    // Pick English or Arabic text. Works for plain strings and {en, ar} arrays.
    public static function t($value): string
    {
        if (is_array($value)) {
            $value = $value[app()->getLocale()] ?? $value['en'] ?? (reset($value) ?: '');
        }

        return (string) $value;
    }

    public static function tr(string $en, string $ar): string
    {
        return app()->getLocale() === 'ar' ? $ar : $en;
    }

    public static function abs(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return str_starts_with($url, 'http') ? $url : url($url);
    }
}