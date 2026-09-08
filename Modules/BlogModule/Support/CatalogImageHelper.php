<?php

namespace Modules\BlogModule\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class CatalogImageHelper
{
    private static ?string $lastError = null;

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    /**
     * Apply a generated temp image to service thumbnail + cover_image.
     */
    public static function applyToService(Service $service, string $tmpPath, bool $replaceBoth = true): bool
    {
        self::$lastError = null;

        if ($tmpPath === '' || !is_file($tmpPath)) {
            self::$lastError = 'Temp image file missing';

            return false;
        }

        try {
            $contents = File::get($tmpPath);
            if ($contents === '' || $contents === false) {
                self::$lastError = 'Temp image file is empty';

                return false;
            }

            $ext = self::detectExtension($tmpPath, $contents);
            $disk = self::resolveDisk();

            $oldCover = (string) ($service->cover_image ?? '');
            $coverName = self::storeBinary('service/', $contents, $ext, $disk, 'svc-cover');
            if ($coverName === null) {
                return false;
            }
            $service->cover_image = $coverName;
            self::safeDelete('service/', $oldCover, $disk);

            if ($replaceBoth) {
                $oldThumb = (string) ($service->thumbnail ?? '');
                $thumbName = self::storeBinary('service/', $contents, $ext, $disk, 'svc-thumb');
                if ($thumbName === null) {
                    return false;
                }
                $service->thumbnail = $thumbName;
                self::safeDelete('service/', $oldThumb, $disk);
            }

            $service->save();

            // Model saved hook may miss isDirty; persist storage metadata explicitly.
            saveSingleImageDataToStorage($service, 'cover_image', $disk);
            if ($replaceBoth) {
                saveSingleImageDataToStorage($service, 'thumbnail', $disk);
            }

            return true;
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            Log::warning('CatalogImageHelper: applyToService failed', [
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Apply a generated temp image to category.image.
     */
    public static function applyToCategory(Category $category, string $tmpPath): bool
    {
        self::$lastError = null;

        if ($tmpPath === '' || !is_file($tmpPath)) {
            self::$lastError = 'Temp image file missing';

            return false;
        }

        try {
            $contents = File::get($tmpPath);
            if ($contents === '' || $contents === false) {
                self::$lastError = 'Temp image file is empty';

                return false;
            }

            $preferred = defined('APPLICATION_IMAGE_FORMAT') ? (string) APPLICATION_IMAGE_FORMAT : 'png';
            $ext = self::detectExtension($tmpPath, $contents, $preferred);
            $disk = self::resolveDisk();
            $old = (string) ($category->image ?? '');

            $filename = self::storeBinary('category/', $contents, $ext, $disk, 'cat');
            if ($filename === null) {
                return false;
            }

            $category->image = $filename;
            $category->save();
            self::safeDelete('category/', $old, $disk);

            if (function_exists('saveSingleImageDataToStorage')) {
                try {
                    saveSingleImageDataToStorage($category, 'image', $disk);
                } catch (\Throwable) {
                    // Category storage relation may be optional.
                }
            }

            return true;
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            Log::warning('CatalogImageHelper: applyToCategory failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private static function resolveDisk(): string
    {
        try {
            $disk = function_exists('getDisk') ? (string) getDisk() : 'public';
        } catch (\Throwable) {
            $disk = 'public';
        }

        return in_array($disk, ['public', 's3'], true) ? $disk : 'public';
    }

    private static function detectExtension(string $tmpPath, string $contents, string $fallback = 'png'): string
    {
        $mime = @mime_content_type($tmpPath) ?: '';
        if (str_starts_with((string) $mime, 'image/')) {
            $map = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            if (isset($map[$mime])) {
                return $map[$mime];
            }
        }

        if (str_starts_with($contents, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($contents, "\x89PNG")) {
            return 'png';
        }
        if (str_starts_with($contents, 'RIFF') && str_contains(substr($contents, 0, 16), 'WEBP')) {
            return 'webp';
        }

        $fromName = strtolower(pathinfo($tmpPath, PATHINFO_EXTENSION));
        if (in_array($fromName, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return $fromName === 'jpeg' ? 'jpg' : $fromName;
        }

        return preg_replace('/[^a-z0-9]/', '', strtolower($fallback)) ?: 'png';
    }

    private static function storeBinary(string $dir, string $contents, string $ext, string $disk, string $prefix): ?string
    {
        $dir = trim($dir, '/').'/';
        $ext = strtolower(preg_replace('/[^a-z0-9]/', '', $ext) ?: 'png');
        $filename = $prefix.'-'.now()->format('YmdHis').'-'.uniqid().'.'.$ext;
        $path = $dir.$filename;

        try {
            if ($disk === 's3') {
                $s3 = app('dynamic.s3');
                if (!$s3) {
                    // Fall back to public if S3 is misconfigured.
                    $disk = 'public';
                } else {
                    if (!$s3->put($path, $contents)) {
                        self::$lastError = 'S3 put returned false for '.$path;

                        return null;
                    }

                    return $filename;
                }
            }

            if (!Storage::disk('public')->put($path, $contents)) {
                self::$lastError = 'Public disk put returned false for '.$path;

                return null;
            }

            if (!Storage::disk('public')->exists($path)) {
                self::$lastError = 'File missing after put: '.$path;

                return null;
            }

            return $filename;
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            Log::warning('CatalogImageHelper: storeBinary failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private static function safeDelete(string $dir, string $oldFilename, string $disk): void
    {
        if ($oldFilename === '' || in_array($oldFilename, ['def.png', 'default.png'], true)) {
            return;
        }

        $path = trim($dir, '/').'/'.ltrim($oldFilename, '/');

        try {
            if ($disk === 's3') {
                $s3 = app('dynamic.s3');
                if ($s3) {
                    $s3->delete($path);
                }
            }
        } catch (\Throwable) {
            // ignore
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
            // ignore — do not fail the new upload if old file cannot be removed
        }
    }
}
