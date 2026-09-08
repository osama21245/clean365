<?php

namespace Modules\Chatbot\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Chat text over Vertex AI Platform / Gemini (Factor-compatible).
 */
class GeminiClient
{
    /**
     * @param  array{google_search?: bool, model?: string}  $options
     */
    public function generateTextForChat(string $prompt, bool $json = true, array $options = []): ?string
    {
        $apiKey = trim((string) config('ai-automation.gemini_text_api_key'));

        if ($apiKey === '') {
            Log::warning('Gemini chat: API key is not configured.');

            return null;
        }

        $models = config('ai-automation.chat_models');
        if (! is_array($models) || $models === []) {
            $models = [(string) config('ai-automation.chat_model', config('ai-automation.gemini_text_model', 'gemini-2.5-flash'))];
        }
        if (! empty($options['model']) && is_string($options['model'])) {
            $models = [$options['model'], ...$models];
        }
        $models = array_values(array_unique(array_filter(array_map(
            static function ($m) {
                $m = trim((string) $m);
                $m = preg_replace('#^models/#', '', $m) ?? $m;

                return $m !== '' ? $m : null;
            },
            $models
        ))));

        $preferredMethod = (string) config('ai-automation.gemini_text_api_method', 'streamGenerateContent');
        $methods = array_values(array_unique(array_filter([
            $preferredMethod,
            'streamGenerateContent',
            'generateContent',
        ])));

        $base = (string) config('ai-automation.gemini_text_api_base', 'https://aiplatform.googleapis.com/v1/publishers/google/models');
        $timeout = (int) config('ai-automation.gemini_text_timeout', 180);
        $useGoogleSearch = (bool) ($options['google_search'] ?? false)
            || (bool) config('ai-automation.gemini_text_google_search');

        $generationConfig = [
            'temperature' => (float) config('ai-automation.chat_temperature', 0.35),
            'maxOutputTokens' => max(512, (int) config('ai-automation.chat_max_output_tokens', 4096)),
            'topP' => (float) config('ai-automation.gemini_text_top_p', 1),
        ];
        if ($json && ! $useGoogleSearch && config('ai-automation.gemini_use_response_mime_type')) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => $generationConfig,
        ];

        if ($useGoogleSearch) {
            $payload['tools'] = [['googleSearch' => (object) []]];
        }

        foreach ($models as $model) {
            foreach ($methods as $method) {
                $url = $this->publisherUrl($base, (string) $model, (string) $method);

                $response = Http::timeout($timeout)
                    ->withQueryParameters(['key' => $apiKey])
                    ->acceptJson()
                    ->post($url, $payload);

                if ($response->successful()) {
                    $text = $this->extractTextFromResponseBody($response->body());
                    if ($text !== null && $text !== '') {
                        return $text;
                    }

                    Log::warning('Gemini chat empty body', [
                        'model' => $model,
                        'method' => $method,
                        'status' => $response->status(),
                        'body' => Str::limit($response->body(), 400),
                    ]);

                    continue;
                }

                Log::warning('Gemini chat model attempt failed', [
                    'model' => $model,
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 400),
                ]);
            }
        }

        return null;
    }

    protected function publisherUrl(string $base, string $model, string $method): string
    {
        $base = rtrim($base, '/');
        if (str_ends_with($base, '/publishers/google/models')) {
            return "{$base}/{$model}:{$method}";
        }

        return "{$base}/publishers/google/models/{$model}:{$method}";
    }

    protected function extractTextFromResponseBody(string $body): ?string
    {
        $body = trim($body);

        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            $text = $this->extractTextFromParsed($decoded);

            if ($text !== null && $text !== '') {
                return trim($text);
            }
        }

        $parts = [];

        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, 'data:')) {
                $line = trim(substr($line, 5));
            }

            if (! str_starts_with($line, '{') && ! str_starts_with($line, '[')) {
                continue;
            }

            $chunk = json_decode($line, true);

            if (! is_array($chunk)) {
                continue;
            }

            $piece = $this->extractTextFromParsed($chunk);

            if ($piece !== null && $piece !== '') {
                $parts[] = $piece;
            }
        }

        if ($parts !== []) {
            return trim(implode('', $parts));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $payload
     */
    protected function extractTextFromParsed(array $payload): ?string
    {
        $segments = [];

        if (array_is_list($payload)) {
            foreach ($payload as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $piece = $this->extractTextFromParsed($item);

                if ($piece !== null && $piece !== '') {
                    $segments[] = $piece;
                }
            }

            return $segments === [] ? null : implode('', $segments);
        }

        $contentParts = data_get($payload, 'candidates.0.content.parts', []);

        if (is_array($contentParts)) {
            foreach ($contentParts as $part) {
                if (! is_array($part)) {
                    continue;
                }

                $text = $part['text'] ?? null;

                if (is_string($text) && $text !== '') {
                    $segments[] = $text;
                }
            }
        }

        foreach ($payload as $value) {
            if (! is_array($value)) {
                continue;
            }

            $piece = $this->extractTextFromParsed($value);

            if ($piece !== null && $piece !== '') {
                $segments[] = $piece;
            }
        }

        return $segments === [] ? null : implode('', $segments);
    }
}
