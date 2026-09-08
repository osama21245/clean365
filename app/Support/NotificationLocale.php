<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Resolve push notification copy. Clean365 users have no ui_locale —
 * use request locale, config('app.locale'), then en / ar.
 */
final class NotificationLocale
{
    /**
     * @return list<string>
     */
    public static function pushLocales(): array
    {
        $configured = config('ad_broadcast.push_locales');
        if (! is_array($configured) || $configured === []) {
            $configured = self::allowedLocales();
        }

        $allowed = self::allowedLocales();

        $locales = array_values(array_unique(array_filter(array_map(
            fn ($l) => self::normalize(is_string($l) ? $l : null),
            $configured
        ))));

        return array_values(array_intersect($locales, $allowed)) ?: ['ar', 'en'];
    }

    /**
     * Locales generated in the creative AI pass (fast). Remaining locales use a lighter translate pass.
     *
     * @return list<string>
     */
    public static function primaryPushLocales(): array
    {
        $configured = config('ad_broadcast.push_primary_locales');
        if (! is_array($configured) || $configured === []) {
            $configured = ['ar', 'en'];
        }

        $primary = array_values(array_unique(array_filter(array_map(
            fn ($l) => self::normalize(is_string($l) ? $l : null),
            $configured
        ))));

        $primary = array_values(array_intersect($primary, self::pushLocales()));

        return $primary !== [] ? $primary : self::pushLocales();
    }

    /**
     * Locales filled by the dedicated translation AI pass.
     *
     * @return list<string>
     */
    public static function secondaryPushLocales(): array
    {
        return array_values(array_diff(self::pushLocales(), self::primaryPushLocales()));
    }

    public static function normalize(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));
        $allowed = self::allowedLocales();

        if ($locale !== '' && in_array($locale, $allowed, true)) {
            return $locale;
        }

        $fallback = (string) config('app.locale', 'en');

        return in_array($fallback, $allowed, true) ? $fallback : 'en';
    }

    /**
     * Request locale, then app locale, then en.
     */
    public static function current(): string
    {
        try {
            $requestLocale = request()?->getPreferredLanguage(self::allowedLocales());
            if (is_string($requestLocale) && $requestLocale !== '') {
                return self::normalize($requestLocale);
            }
        } catch (\Throwable) {
            //
        }

        return self::normalize((string) app()->getLocale());
    }

    /**
     * @return array<string, string>
     */
    public static function decodeMap(mixed $stored): array
    {
        if (is_array($stored)) {
            return self::sanitizeMap($stored);
        }

        if (! is_string($stored) || trim($stored) === '') {
            return [];
        }

        $trimmed = trim($stored);
        if (! str_starts_with($trimmed, '{')) {
            return ['en' => $trimmed];
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? self::sanitizeMap($decoded) : ['en' => $trimmed];
    }

    /**
     * @param  array<string, string>|string  $text
     */
    public static function encodeForStorage(array|string $text): string
    {
        if (is_string($text)) {
            return $text;
        }

        return json_encode(self::sanitizeMap($text), JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    public static function usesLocalizedStorage(mixed $stored): bool
    {
        $map = self::decodeMap($stored);

        return count($map) > 1 || (count($map) === 1 && ! array_key_exists('en', $map));
    }

    /**
     * @param  array<string, string>|string  $text
     */
    public static function resolve(array|string $text, ?string $locale): string
    {
        if (is_string($text)) {
            return $text;
        }

        $map = self::sanitizeMap($text);
        $locale = self::normalize($locale);

        if (isset($map[$locale]) && $map[$locale] !== '') {
            return $map[$locale];
        }

        if (isset($map['en']) && $map['en'] !== '') {
            return $map['en'];
        }

        if (isset($map['ar']) && $map['ar'] !== '') {
            return $map['ar'];
        }

        return (string) reset($map);
    }

    /**
     * Pick a locale key from a title map when the user has no ui_locale.
     *
     * @param  array<string, mixed>  $title
     */
    public static function pickTitleLocale(array $title): string
    {
        $preferred = self::normalize((string) config('app.locale', 'en'));
        if (array_key_exists($preferred, $title) && trim((string) $title[$preferred]) !== '') {
            return $preferred;
        }

        if (isset($title['en']) && trim((string) $title['en']) !== '') {
            return 'en';
        }

        if (isset($title['ar']) && trim((string) $title['ar']) !== '') {
            return 'ar';
        }

        $first = array_key_first($title);

        return is_string($first) ? $first : 'en';
    }

    /**
     * @return list<string>
     */
    public static function allowedLocales(): array
    {
        $allowed = config('app.available_locales', ['ar', 'en']);
        if (! is_array($allowed) || $allowed === []) {
            $allowed = ['ar', 'en'];
        }

        return array_values(array_filter(array_map(
            fn ($l) => strtolower(trim((string) $l)),
            $allowed
        )));
    }

    /**
     * @param  array<string, mixed>  $map
     * @return array<string, string>
     */
    private static function sanitizeMap(array $map): array
    {
        $allowed = self::allowedLocales();

        $out = [];
        foreach ($map as $locale => $value) {
            $locale = strtolower(trim((string) $locale));
            if (! in_array($locale, $allowed, true)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $out[$locale] = Str::limit($value, 255, '');
        }

        return $out;
    }
}
