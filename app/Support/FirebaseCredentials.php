<?php

namespace App\Support;

/**
 * Resolve Firebase service-account credentials from env.
 *
 * Accepted values for FIREBASE_CREDENTIALS / FIREBASE_SECONDARY_CREDENTIALS:
 * - relative path (resolved with base_path), e.g. storage/app/private/google-service-account.json
 * - absolute path to a JSON file
 * - raw JSON object string starting with {
 * - base64-encoded JSON (Dokploy-friendly single line)
 */
class FirebaseCredentials
{
    /**
     * @return array<string, mixed>|string|null Path string or decoded service-account array.
     */
    public static function resolve(?string $value): array|string|null
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : null;
        }

        // Prefer file path when it looks like one (keeps local .env working).
        $looksLikePath = str_contains($value, '.json')
            || str_contains($value, DIRECTORY_SEPARATOR)
            || str_contains($value, '/')
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $value) === 1;

        if ($looksLikePath) {
            $path = self::absolutePath($value);
            if (is_file($path)) {
                return $path;
            }
        }

        $binary = base64_decode($value, true);
        if ($binary !== false && str_starts_with(ltrim($binary), '{')) {
            $decoded = json_decode($binary, true);

            return is_array($decoded) ? $decoded : null;
        }

        if (! $looksLikePath) {
            $path = self::absolutePath($value);
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function asArray(?string $value): ?array
    {
        $resolved = self::resolve($value);

        if (is_array($resolved)) {
            return $resolved;
        }

        if (is_string($resolved) && is_file($resolved)) {
            $decoded = json_decode((string) file_get_contents($resolved), true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    public static function absolutePath(string $path): string
    {
        $trimmed = trim($path);

        if (
            str_starts_with($trimmed, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $trimmed) === 1
        ) {
            return $trimmed;
        }

        return base_path($trimmed);
    }
}
