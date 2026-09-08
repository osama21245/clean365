<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateTranslations extends Command
{
    protected $signature = 'translations:generate';
    protected $description = 'Scan PHP files and generate translation keys from translate() calls and response constants';

    public function handle()
    {
        $locale = 'en';
        $langPath = resource_path("lang/{$locale}/lang.php");
        $translations = File::exists($langPath)
            ? include($langPath)
            : [];

        $translations = collect($translations)
            ->mapWithKeys(fn($value, $key) => [(string) $key => $value])
            ->toArray();

        $paths = [
            app_path(),
            resource_path(),
            base_path('routes'),
            base_path('Modules')];

        $newKeys = [];
        $discoveredKeys = [];

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if (!$this->isPhpFile($file->getFilename())) {
                    continue;
                }

                $content = File::get($file);
                foreach ($this->extractTranslateKeys($content) as $key) {
                    $discoveredKeys[$key] = $this->defaultTranslationValue($key);
                }
            }
        }

        foreach ($this->extractResponseConstantTranslations() as $key => $message) {
            $discoveredKeys[$key] = $message;
        }

        foreach ($discoveredKeys as $key => $value) {
            if (!isset($translations[$key])) {
                $translations[$key] = $value;
                $newKeys[] = $key;
                continue;
            }

            if ($this->shouldRefreshExistingValue($key, $translations[$key], $value)) {
                $translations[$key] = $value;
            }
        }

        uksort($translations, function ($a, $b) {
            return strcmp((string)$a, (string)$b);
        });

        $export = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

        File::put($langPath, $export);

        $this->info(count($newKeys) . " new translation keys added.");
    }

    private function isPhpFile(string $filename): bool
    {
        return str_ends_with($filename, '.php');
    }

    private function extractTranslateKeys(string $content): array
    {
        preg_match_all('/translate\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1\s*(?:,|\))/s', $content, $matches);

        $keys = [];

        foreach ($matches[2] ?? [] as $rawKey) {
            $key = stripcslashes((string)$rawKey);
            $key = str_replace(["\n", "\r"], ' ', $key);
            $key = preg_replace('/\s+/', ' ', $key);
            $key = trim($key);

            if ($key !== '') {
                $keys[$key] = $key;
            }
        }

        return array_values($keys);
    }

    private function extractResponseConstantTranslations(): array
    {
        $responseFiles = [
            app_path('Lib/Response.php'),
            base_path('Modules/PaymentModule/Library/Responses.php')];

        $translations = [];

        foreach ($responseFiles as $filePath) {
            if (!File::exists($filePath)) {
                continue;
            }

            $content = File::get($filePath);
            preg_match_all('/const\s+[A-Z0-9_]+\s*=\s*\[(.*?)\];/s', $content, $constantMatches);

            foreach ($constantMatches[1] ?? [] as $constantBody) {
                preg_match("/'key'\s*=>\s*'([^']+)'/", $constantBody, $keyMatch);
                preg_match("/'message'\s*=>\s*'([^']*)'/", $constantBody, $messageMatch);

                if (empty($keyMatch[1]) || !array_key_exists(1, $messageMatch)) {
                    continue;
                }

                $translations[$keyMatch[1]] = $messageMatch[1];
            }
        }

        return $translations;
    }

    private function defaultTranslationValue(string $key): string
    {
        if ($this->looksLikeUnderscoreKey($key)) {
            return trim((string) preg_replace('/\s+/', ' ', str_replace('_', ' ', $key)));
        }

        return $key;
    }

    private function looksLikeUnderscoreKey(string $key): bool
    {
        return str_contains($key, '_');
    }

    private function shouldRefreshExistingValue(string $key, mixed $currentValue, string $newValue): bool
    {
        if (!is_string($currentValue)) {
            return false;
        }

        if ($this->looksLikeUnderscoreKey($key) && $currentValue === $key) {
            return true;
        }

        return false;
    }
}
