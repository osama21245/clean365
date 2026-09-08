<?php

namespace Modules\Chatbot\Services\Ai;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Chat-oriented LLM client with Gemini → OpenAI failover (Factor/Elsorady pattern).
 */
class ChatLlmClient
{
    public function __construct(
        protected GeminiClient $gemini,
        protected OpenAiTextGenerator $openAi,
    ) {}

    /**
     * @return array{ok:bool, endpoint_failed:bool, json:?array, text:string, provider:?string, status:?int}
     */
    public function complete(string $prompt, bool $expectJson = true, array $options = []): array
    {
        $gemini = $this->attemptGemini($prompt, $expectJson, $options);
        if ($gemini['ok']) {
            return $gemini;
        }

        $openAi = $this->attemptOpenAi($prompt, $expectJson);
        if ($openAi['ok']) {
            return $openAi;
        }

        if ($expectJson) {
            $plain = $this->attemptOpenAi($prompt, false);
            if ($plain['ok']) {
                return $plain;
            }
        }

        Log::error('Clean365 chat LLM failed after Gemini/OpenAI failover', [
            'expect_json' => $expectJson,
        ]);

        return [
            'ok' => false,
            'endpoint_failed' => true,
            'json' => null,
            'text' => '',
            'provider' => null,
            'status' => $openAi['status'] ?? $gemini['status'] ?? null,
        ];
    }

    /**
     * @return array{ok:bool, endpoint_failed:bool, json:?array, text:string, provider:?string, status:?int}
     */
    private function attemptGemini(string $prompt, bool $expectJson, array $options): array
    {
        if (! config('ai-automation.gemini_text_api_key')) {
            return $this->failed('gemini');
        }

        try {
            $raw = $this->gemini->generateTextForChat($prompt, $expectJson, $options);
        } catch (Throwable $e) {
            Log::warning('Gemini chat failed', ['error' => $e->getMessage()]);

            return $this->failed('gemini');
        }

        if ($raw === null || trim($raw) === '') {
            return $this->failed('gemini');
        }

        return $this->parseText($raw, $expectJson, 'gemini');
    }

    /**
     * @return array{ok:bool, endpoint_failed:bool, json:?array, text:string, provider:?string, status:?int}
     */
    private function attemptOpenAi(string $prompt, bool $expectJson): array
    {
        if (! config('ai-automation.openai_api_key') && ! config('services.openai.api_key')) {
            return $this->failed('openai');
        }

        try {
            $raw = $this->openAi->generateText($prompt, $expectJson);
        } catch (Throwable $e) {
            Log::warning('OpenAI chat failed', ['error' => $e->getMessage()]);

            return $this->failed('openai');
        }

        if ($raw === null || trim($raw) === '') {
            return $this->failed('openai');
        }

        return $this->parseText($raw, $expectJson, 'openai');
    }

    /**
     * @return array{ok:bool, endpoint_failed:bool, json:?array, text:string, provider:?string, status:?int}
     */
    private function parseText(string $text, bool $expectJson, string $provider): array
    {
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
            $text = trim($text);
        }

        $json = null;
        if ($expectJson) {
            $json = $this->decodeJson($text);
            if (! is_array($json)) {
                return [
                    'ok' => true,
                    'endpoint_failed' => false,
                    'json' => null,
                    'text' => $text,
                    'provider' => $provider,
                    'status' => 200,
                ];
            }
        }

        $assistant = '';
        if (is_array($json)) {
            foreach (['message', 'answer', 'reply', 'text', 'content'] as $key) {
                if (! empty($json[$key]) && is_string($json[$key])) {
                    $assistant = trim($json[$key]);
                    break;
                }
            }
        }

        if ($assistant === '') {
            $assistant = $expectJson && is_array($json)
                ? (json_encode($json, JSON_UNESCAPED_UNICODE) ?: '')
                : $text;
        }

        return [
            'ok' => true,
            'endpoint_failed' => false,
            'json' => $json,
            'text' => $assistant,
            'provider' => $provider,
            'status' => 200,
        ];
    }

    private function decodeJson(string $text): ?array
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @return array{ok:bool, endpoint_failed:bool, json:?array, text:string, provider:?string, status:?int}
     */
    private function failed(string $provider): array
    {
        return [
            'ok' => false,
            'endpoint_failed' => true,
            'json' => null,
            'text' => '',
            'provider' => $provider,
            'status' => null,
        ];
    }
}
