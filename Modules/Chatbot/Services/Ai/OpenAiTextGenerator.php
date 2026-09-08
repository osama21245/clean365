<?php

namespace Modules\Chatbot\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAiTextGenerator
{
    public function generateText(string $prompt, bool $json = false): ?string
    {
        $apiKey = config('ai-automation.openai_api_key') ?: config('services.openai.api_key');

        if (! $apiKey) {
            Log::warning('OPENAI_API_KEY is not configured.');

            return null;
        }

        $payload = [
            'model' => config('ai-automation.openai_text_model', config('services.openai.chat_model', 'gpt-4o-mini')),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ];

        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::timeout(120)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (! $response->successful()) {
                Log::warning('OpenAI text failed', [
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 500),
                ]);

                return null;
            }

            $text = data_get($response->json(), 'choices.0.message.content');

            if (! $text) {
                return null;
            }

            $text = trim($text);

            if (str_starts_with($text, '```')) {
                $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
                $text = preg_replace('/\s*```$/', '', $text);
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::warning('OpenAI text exception: '.$e->getMessage());

            return null;
        }
    }
}
