<?php

namespace Modules\BlogModule\Services\Gemini;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Google Gemini via Gemini API (generativelanguage.googleapis.com) or Vertex AI Platform (aiplatform).
 */
class GeminiVertexClient
{
    public function resolveTextApiKey(): string
    {
        // AI Studio (generativelanguage): prefer AIza keys; Vertex AQ.* last.
        // Vertex (aiplatform): prefer dedicated text / vertex keys first.
        $candidates = $this->textUsesAiPlatform()
            ? [
                config('services.gemini.text_api_key'),
                config('services.gemini.vertex_api_key'),
                config('services.gemini.api_key'),
                config('services.gemini.image_api_key'),
            ]
            : [
                config('services.gemini.api_key'),
                config('services.gemini.text_api_key'),
                config('services.gemini.seo_text_api_key'),
                config('services.gemini.image_api_key'),
                config('services.gemini.vertex_api_key'),
            ];

        $fallback = '';
        foreach ($candidates as $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            if (!$this->textUsesAiPlatform() && str_starts_with($key, 'AQ.')) {
                if ($fallback === '') {
                    $fallback = $key;
                }

                continue;
            }

            if (!$this->textUsesAiPlatform() && !str_starts_with($key, 'AIza')) {
                if ($fallback === '') {
                    $fallback = $key;
                }

                continue;
            }

            return $key;
        }

        return $fallback;
    }

    public function resolveImageApiKey(): string
    {
        // Gemini API (AI Studio / generativelanguage): prefer AIza keys; Vertex AQ.* last.
        // Vertex (aiplatform): prefer dedicated image / vertex keys first.
        $candidates = $this->imageUsesAiPlatform()
            ? [
                config('services.gemini.image_api_key'),
                config('services.gemini.vertex_api_key'),
                config('services.gemini.text_api_key'),
                config('services.gemini.api_key'),
            ]
            : [
                config('services.gemini.api_key'),
                config('services.gemini.text_api_key'),
                config('services.gemini.image_api_key'),
                config('services.gemini.vertex_api_key'),
            ];

        $fallback = '';
        foreach ($candidates as $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            // On AI Studio, AQ.* (Vertex/Express) keys are often blocked for GenerateContent.
            if (!$this->imageUsesAiPlatform() && str_starts_with($key, 'AQ.')) {
                if ($fallback === '') {
                    $fallback = $key;
                }

                continue;
            }

            // On AI Studio, prefer AIza keys when available.
            if (!$this->imageUsesAiPlatform() && !str_starts_with($key, 'AIza')) {
                if ($fallback === '') {
                    $fallback = $key;
                }

                continue;
            }

            return $key;
        }

        return $fallback;
    }

    public function textUsesAiPlatform(): bool
    {
        return strtolower((string) config('services.gemini.text_endpoint_style', 'aiplatform')) === 'aiplatform';
    }

    public function imageUsesAiPlatform(): bool
    {
        return strtolower((string) config('services.gemini.image_endpoint_style', 'generativelanguage')) === 'aiplatform';
    }

    public function textBaseUrl(): string
    {
        return rtrim(
            (string) config(
                'services.gemini.text_base_url',
                'https://aiplatform.googleapis.com/v1/publishers/google/models'
            ),
            '/'
        );
    }

    public function imageBaseUrl(): string
    {
        $default = $this->imageUsesAiPlatform()
            ? 'https://aiplatform.googleapis.com/v1/publishers/google/models'
            : 'https://generativelanguage.googleapis.com/v1beta';

        return rtrim(
            (string) config('services.gemini.image_base_url', $default),
            '/'
        );
    }

    public function seoUsesAiPlatform(): bool
    {
        $style = trim((string) config('services.gemini.seo_text_endpoint_style'));
        if ($style !== '') {
            return strtolower($style) === 'aiplatform';
        }

        $seoKey = trim((string) config('services.gemini.seo_text_api_key'));
        if ($seoKey !== '' && str_starts_with($seoKey, 'AIza')) {
            return false;
        }

        return $this->textUsesAiPlatform();
    }

    /**
     * @return array{api_key: string, use_ai_platform: bool}|null
     */
    public function seoTextCallOptions(): ?array
    {
        $apiKey = trim((string) config('services.gemini.seo_text_api_key'));
        if ($apiKey === '') {
            return null;
        }

        return [
            'api_key' => $apiKey,
            'use_ai_platform' => $this->seoUsesAiPlatform(),
        ];
    }

    public function buildTextRequestUrl(
        string $model,
        string $endpoint,
        ?string $apiKey = null,
        ?bool $useAiPlatform = null,
    ): string {
        $apiKey ??= $this->resolveTextApiKey();
        $useAiPlatform ??= $this->textUsesAiPlatform();

        if ($useAiPlatform) {
            return $this->textBaseUrl()."/{$model}:{$endpoint}?key={$apiKey}";
        }

        $base = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        return "{$base}/models/{$model}:{$endpoint}?key={$apiKey}";
    }

    public function buildImageRequestUrl(string $model, string $endpoint, ?string $apiKey = null): string
    {
        $apiKey ??= $this->resolveImageApiKey();

        if ($this->imageUsesAiPlatform()) {
            return $this->imageBaseUrl()."/{$model}:{$endpoint}?key={$apiKey}";
        }

        // Gemini API: https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
        $base = $this->imageBaseUrl();
        if ($base === '' || str_contains($base, 'aiplatform.googleapis.com')) {
            $base = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        }

        return "{$base}/models/{$model}:{$endpoint}?key={$apiKey}";
    }

    /**
     * @param  array<string, mixed>  $generationConfig
     * @return array<string, mixed>
     */
    public function buildTextPayload(string $prompt, array $generationConfig, ?bool $useAiPlatform = null): array
    {
        $useAiPlatform ??= $this->textUsesAiPlatform();

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => $this->adaptTextGenerationConfig($generationConfig, $useAiPlatform),
        ];

        if ($useAiPlatform && config('services.gemini.text_google_search')) {
            $payload['tools'] = [
                ['googleSearch' => (object) []],
            ];
        }

        return $payload;
    }

    /**
     * Vertex text API may reject responseMimeType / thinkingConfig from AI Studio.
     *
     * @param  array<string, mixed>  $generationConfig
     * @return array<string, mixed>
     */
    public function adaptTextGenerationConfig(array $generationConfig, ?bool $useAiPlatform = null): array
    {
        $useAiPlatform ??= $this->textUsesAiPlatform();

        if (! $useAiPlatform) {
            return $generationConfig;
        }

        unset($generationConfig['thinkingConfig']);

        if (! filter_var(config('services.gemini.use_response_mime_type', false), FILTER_VALIDATE_BOOLEAN)) {
            unset($generationConfig['responseMimeType']);
        }

        if (! array_key_exists('topP', $generationConfig)) {
            $generationConfig['topP'] = (float) config('services.gemini.text_top_p', 1);
        }

        return $generationConfig;
    }

    /**
     * @return list<string>
     */
    public function legacyTextBaseUrlCandidates(): array
    {
        $configuredBase = rtrim((string) config('services.gemini.base_url'), '/');
        $baseCandidates = [$configuredBase];
        $v1FromBeta = preg_replace('#/v1beta$#', '/v1', $configuredBase);

        if ($v1FromBeta !== $configuredBase && $v1FromBeta !== '') {
            $baseCandidates[] = $v1FromBeta;
        }

        $baseCandidates[] = 'https://generativelanguage.googleapis.com/v1beta';
        $baseCandidates[] = 'https://generativelanguage.googleapis.com/v1';

        return array_values(array_unique(array_filter($baseCandidates)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseStreamBodyChunks(string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            if (isset($decoded['candidates'])) {
                return [$decoded];
            }

            $allList = true;
            foreach ($decoded as $item) {
                if (! is_array($item)) {
                    $allList = false;
                    break;
                }
            }
            if ($allList && $decoded !== []) {
                /** @var list<array<string, mixed>> $decoded */
                return $decoded;
            }
        }

        $out = [];
        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, 'data:')) {
                $line = trim(substr($line, 5));
                if ($line === '[DONE]') {
                    continue;
                }
            }
            $row = json_decode($line, true);
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    public function extractTextFromChunks(array $chunks): string
    {
        $text = '';

        foreach ($chunks as $chunk) {
            $parts = data_get($chunk, 'candidates.0.content.parts', []);
            if (! is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                    $text .= $part['text'];
                }
            }
        }

        return trim($text);
    }

    /**
     * Normalize stream / single JSON into one structure compatible with extractCandidateText().
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array<string, mixed>
     */
    public function mergedCandidateResponse(array $chunks): array
    {
        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $this->extractTextFromChunks($chunks)],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * POST and return merged candidate response, or null on 4xx / empty text.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|null
     */
    public function postTextAndMerge(string $url, array $body, ?\Throwable &$lastException = null): ?array
    {
        try {
            $timeout = max(60, (int) config('services.gemini.http_timeout', 180));
            $connectTimeout = max(30, (int) config('services.gemini.connect_timeout', 60));

            $httpResponse = Http::acceptJson()
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->post($url, $body);

            if (! $httpResponse->successful()) {
                $lastException = $httpResponse->toException();

                if (in_array($httpResponse->status(), [400, 403, 404], true)) {
                    return null;
                }

                throw $lastException;
            }

            $chunks = $this->parseStreamBodyChunks((string) $httpResponse->body());
            $text = $this->extractTextFromChunks($chunks);

            if ($text === '') {
                $finish = (string) data_get($chunks, '0.candidates.0.finishReason', '');
                $block = (string) data_get($chunks, '0.promptFeedback.blockReason', '');
                $snippet = Str::limit((string) $httpResponse->body(), 400);
                $lastException = new \RuntimeException(
                    'Gemini returned empty text'
                    .($finish !== '' ? " (finishReason={$finish})" : '')
                    .($block !== '' ? " (blockReason={$block})" : '')
                    .($snippet !== '' ? ": {$snippet}" : '')
                );

                return null;
            }

            return $this->mergedCandidateResponse($chunks);
        } catch (RequestException $e) {
            $lastException = $e;
            if (in_array($e->response?->status(), [400, 403, 404], true)) {
                return null;
            }

            throw $e;
        }
    }

    public function normalizeModelId(string $model): string
    {
        $model = trim($model);

        if ($model === '') {
            return '';
        }

        return preg_replace('#^models/#', '', $model) ?? $model;
    }

    /**
     * Try Vertex stream + generate, then legacy bases. Returns merged API response.
     *
     * @param  list<string>  $models
     * @param  array<string, mixed>  $generationConfig
     * @return array<string, mixed>
     */
    public function generateText(
        string $prompt,
        array $models,
        array $generationConfig,
        string $failurePrefix,
        ?array $options = null,
    ): array {
        $apiKey = trim((string) ($options['api_key'] ?? ''));
        if ($apiKey === '') {
            $apiKey = $this->resolveTextApiKey();
        }
        if ($apiKey === '') {
            throw new \RuntimeException('GEMINI_TEXT_API_KEY, GEMINI_VERTEX_API_KEY, or GEMINI_API_KEY is not configured.');
        }

        $useAiPlatform = array_key_exists('use_ai_platform', $options ?? [])
            ? (bool) $options['use_ai_platform']
            : $this->textUsesAiPlatform();

        $body = $this->buildTextPayload($prompt, $generationConfig, $useAiPlatform);
        $lastException = null;
        $tried = [];
        $endpoints = ['streamGenerateContent', 'generateContent'];

        if ($useAiPlatform) {
            foreach ($models as $model) {
                $model = $this->normalizeModelId($model);
                if ($model === '') {
                    continue;
                }
                foreach ($endpoints as $endpoint) {
                    $url = $this->buildTextRequestUrl($model, $endpoint, $apiKey, $useAiPlatform);
                    $tried[] = $url;
                    $response = $this->postTextAndMerge($url, $body, $lastException);
                    if (is_array($response)) {
                        return $response;
                    }
                }
            }
        }

        // AI Studio fallback must use an AIza key — AQ.* Vertex keys are often blocked here.
        $studioKey = $this->resolveAiStudioApiKey() ?: $apiKey;
        $studioBody = $this->buildTextPayload($prompt, $generationConfig, false);

        foreach ($this->legacyTextBaseUrlCandidates() as $baseUrl) {
            foreach ($models as $model) {
                $model = $this->normalizeModelId($model);
                if ($model === '') {
                    continue;
                }
                $url = "{$baseUrl}/models/{$model}:generateContent?key={$studioKey}";
                $tried[] = $url;
                $response = $this->postTextAndMerge($url, $studioBody, $lastException);
                if (is_array($response)) {
                    return $response;
                }
            }
        }

        $detail = $lastException?->getMessage() ?: 'no HTTP detail captured';
        if ($lastException instanceof RequestException) {
            $status = $lastException->response?->status();
            $body = Str::limit((string) ($lastException->response?->body() ?? ''), 500);
            $detail = trim(($status ? "HTTP {$status}: " : '').$body);
        }

        throw new \RuntimeException(
            "{$failurePrefix}. {$detail}. Tried: ".implode(' | ', array_map(fn ($u) => Str::before($u, '?key='), $tried)),
            previous: $lastException
        );
    }

    /**
     * Prefer Google AI Studio (AIza...) keys for generativelanguage.googleapis.com.
     */
    public function resolveAiStudioApiKey(): string
    {
        foreach ([
            config('services.gemini.api_key'),
            config('services.gemini.seo_text_api_key'),
            config('services.gemini.text_api_key'),
            config('services.gemini.image_api_key'),
        ] as $key) {
            $key = trim((string) $key);
            if ($key !== '' && str_starts_with($key, 'AIza')) {
                return $key;
            }
        }

        return '';
    }
}
