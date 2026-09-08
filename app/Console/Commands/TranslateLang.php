<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateLang extends Command
{
    protected $signature = 'translations:auto {from=en} {to=bn}';
    protected $description = 'Auto translate lang.php using Google API';

    public function handle()
    {
        $from = $this->argument('from');
        $to = $this->argument('to');

        $sourcePath = resource_path("lang/{$from}/lang.php");
        $targetPath = resource_path("lang/{$to}/lang.php");

        if (!\File::exists($sourcePath)) {
            $this->error("Source file not found!");
            return;
        }

        if (\File::exists($targetPath)) {
            \File::delete($targetPath);
            $this->info("Old {$to}/lang.php deleted.");
        }

        $source = include($sourcePath);
        $translated = [];

        $total = count($source);
        $this->info("Total keys: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $concurrency = 50;
        $chunks = array_chunk($source, $concurrency, true);

        foreach ($chunks as $chunk) {

            $multiHandle = curl_multi_init();
            $handles = [];

            foreach ($chunk as $key => $text) {
                $normalized = $this->normalizeTranslationSource((string) $text);

                if ($normalized === '') {
                    $translated[$key] = '';
                    $bar->advance();
                    continue;
                }

                $prepared = $this->maskTranslationTokens($normalized);
                $query = http_build_query([
                    'client' => 'gtx',
                    'ie' => 'UTF-8',
                    'oe' => 'UTF-8',
                    'sl' => $from,
                    'tl' => $to,
                    'hl' => 'hl',
                    'q' => $prepared['text']]) . '&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at';

                $ch = curl_init('https://translate.googleapis.com/translate_a/single?' . $query);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);

                curl_multi_add_handle($multiHandle, $ch);

                $handles[] = [
                    'handle' => $ch,
                    'key' => $key,
                    'tokens' => $prepared['tokens'],
                    'source' => $normalized];
            }

            $running = null;
            do {
                curl_multi_exec($multiHandle, $running);
                curl_multi_select($multiHandle);
            } while ($running > 0);

            foreach ($handles as $item) {

                $ch = $item['handle'];
                $key = $item['key'];

                $response = curl_multi_getcontent($ch);

                if (!$response) {
                    $translated[$key] = $item['source'];
                } else {
                    $result = json_decode($response, true);
                    $segments = collect($result[0] ?? [])
                        ->pluck(0)
                        ->filter(fn ($segment) => filled($segment))
                        ->implode('');

                    $text = $segments !== '' ? $segments : $item['source'];
                    $text = $this->restoreTranslationTokens(
                        $this->normalizeTranslationSource(str_replace('_', ' ', (string) $text)),
                        $item['tokens']
                    );

                    $translated[$key] = $text ?: $item['source'];
                }

                curl_multi_remove_handle($multiHandle, $ch);

                $bar->advance();
            }

            curl_multi_close($multiHandle);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Saving translated file...");

        \File::ensureDirectoryExists(dirname($targetPath));

        $export = "<?php\n\nreturn " . var_export($translated, true) . ";\n";

        \File::put($targetPath, $export);

        $this->info("Translation completed: {$to}");
    }

    private function normalizeTranslationSource(string $text): string
    {
        return normalize_translation_text($text);
    }

    private function maskTranslationTokens(string $text): array
    {
        $tokens = [];
        $index = 0;

        $maskedText = preg_replace_callback(
            '/(\{\{[^}]+\}\}|:\w+|%\d*\$?[sd])/u',
            function ($matches) use (&$tokens, &$index) {
                $token = 'ZXQPH' . $index . 'QXZ';
                $tokens[$token] = $matches[0];
                $index++;

                return $token;
            },
            $text
        );

        return [
            'text' => $maskedText,
            'tokens' => $tokens];
    }

    private function restoreTranslationTokens(string $text, array $tokens): string
    {
        return strtr($text, $tokens);
    }
}
