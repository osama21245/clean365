<?php

namespace Modules\BlogModule\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\BlogModule\Entities\Article;
use Modules\ServiceManagement\Entities\Service;

class FeaturedImageHelper
{
    /**
     * Blog images are always stored on the public disk so the website can serve them at /storage/blog/.
     */
    public static function blogDisk(): string
    {
        return 'public';
    }

    /**
     * Save a temp image file path onto the article featured_image column.
     */
    public static function saveTempFile(Article $article, string $tmpPath, bool $deletePrevious = false): bool
    {
        if ($tmpPath === '' || !is_file($tmpPath)) {
            return false;
        }

        try {
            $previous = $article->featured_image;
            $contents = File::get($tmpPath);
            $stored = self::storeBinary($article, $contents, 'ai');

            if (!$stored) {
                return false;
            }

            if ($deletePrevious && is_string($previous) && $previous !== '' && $previous !== $stored) {
                self::deleteStoredImage($previous);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('FeaturedImageHelper: saveTempFile failed', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Copy service thumbnail/cover as featured image fallback.
     */
    public static function copyFromService(Article $article, Service $service): bool
    {
        $source = $service->thumbnail ?: $service->cover_image;
        if (!$source) {
            Log::warning('FeaturedImageHelper: service has no thumbnail/cover', [
                'article_id' => $article->id,
                'service_id' => $service->id,
            ]);

            return false;
        }

        try {
            $contents = self::resolveServiceImageContents($service, (string) $source);
            if ($contents === null || $contents === '') {
                Log::warning('FeaturedImageHelper: could not read service image bytes', [
                    'article_id' => $article->id,
                    'service_id' => $service->id,
                    'source' => $source,
                ]);

                return false;
            }

            $ext = pathinfo(parse_url($source, PHP_URL_PATH) ?: $source, PATHINFO_EXTENSION) ?: 'png';
            $filename = self::storeBinary($article, $contents, 'svc', $ext);

            return $filename !== null;
        } catch (\Throwable $e) {
            Log::warning('FeaturedImageHelper: copyFromService failed', [
                'article_id' => $article->id,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return string|null Stored filename
     */
    public static function storeBinary(Article $article, string $contents, string $prefix = 'ai', string $extension = 'png'): ?string
    {
        if ($contents === '') {
            return null;
        }

        $disk = self::blogDisk();
        $extension = strtolower(preg_replace('/[^a-z0-9]/', '', $extension) ?: 'png');
        if ($extension === '') {
            $extension = 'png';
        }

        $filename = $prefix . '-' . $article->id . '-' . time() . '.' . $extension;
        $path = 'blog/' . $filename;

        try {
            if (!Storage::disk($disk)->put($path, $contents)) {
                Log::warning('FeaturedImageHelper: put returned false', [
                    'article_id' => $article->id,
                    'path' => $path,
                    'disk' => $disk,
                ]);

                return null;
            }

            if (!Storage::disk($disk)->exists($path)) {
                Log::warning('FeaturedImageHelper: file missing after put', [
                    'article_id' => $article->id,
                    'path' => $path,
                    'disk' => $disk,
                ]);

                return null;
            }

            $article->featured_image = $filename;
            $article->save();
            saveSingleImageDataToStorage($article, 'featured_image', $disk);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('FeaturedImageHelper: storeBinary failed', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function deleteStoredImage(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $path = 'blog/' . ltrim($filename, '/');

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('FeaturedImageHelper: deleteStoredImage failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function resolvePublicUrl(string $filename, ?string $disk = null): ?string
    {
        if ($filename === '') {
            return null;
        }

        $path = 'blog/' . ltrim($filename, '/');

        // Prefer local public disk (website serves /storage/blog/...)
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/blog/' . ltrim($filename, '/'));
        }

        if ($disk === 's3') {
            try {
                $s3Disk = app('dynamic.s3');
                if ($s3Disk && $s3Disk->exists($path)) {
                    return $s3Disk->url($path);
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        return null;
    }

    public static function hasUsableImage(Article $article): bool
    {
        $filename = (string) ($article->featured_image ?? '');
        if ($filename === '') {
            return false;
        }

        return self::resolvePublicUrl($filename, $article->storage_featured_image?->storage_type) !== null;
    }

    private static function resolveServiceImageContents(Service $service, string $source): ?string
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::timeout(20)->get($source);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Throwable) {
                // Try disk paths below.
            }
        }

        $paths = [
            'service/' . ltrim($source, '/'),
            ltrim($source, '/'),
        ];

        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->get($path);
            }

            try {
                $s3Disk = app('dynamic.s3');
                if ($s3Disk && $s3Disk->exists($path)) {
                    return $s3Disk->get($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $publicPath = public_path('storage/service/' . ltrim($source, '/'));
        if (is_file($publicPath)) {
            return File::get($publicPath);
        }

        $full = $service->thumbnail_full_path ?? $service->cover_image_full_path ?? null;
        if (is_string($full) && $full !== '' && filter_var($full, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::timeout(20)->get($full);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
