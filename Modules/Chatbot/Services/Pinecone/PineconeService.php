<?php

namespace Modules\Chatbot\Services\Pinecone;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PineconeService
{
    private HttpFactory $http;

    public function __construct(HttpFactory $http)
    {
        $this->http = $http;
    }

    public function isConfigured(): bool
    {
        return (string) config('services.pinecone.api_key') !== ''
            && (string) config('services.pinecone.index_host') !== '';
    }

    public function upsertRecord(array $record): void
    {
        if (! $this->isConfigured()) {
            Log::debug('Pinecone not configured; skipping upsertRecord.');

            return;
        }

        $schemaVersion = $record['_schema_version'] ?? 'clean365_packages_v1';
        $record = $this->sanitizeRecord($record);
        $record['_schema_version'] = $schemaVersion;
        $namespace = rawurlencode($this->namespace());
        $url = rtrim($this->host(), '/').'/records/namespaces/'.$namespace.'/upsert';
        $ndjson = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";

        $response = $this->http->timeout(120)
            ->withHeaders($this->headers())
            ->withBody($ndjson, 'application/x-ndjson')
            ->post($url);

        $this->throwWithContext($response, $url, ['ndjson' => $ndjson]);
    }

    public function upsertRecords(array $records): ?Response
    {
        if ($records === []) {
            throw new RuntimeException('No records to upsert.');
        }

        if (! $this->isConfigured()) {
            Log::debug('Pinecone not configured; skipping upsertRecords.');

            return null;
        }

        $sanitized = array_map(fn (array $record) => $this->sanitizeRecord($record), $records);
        $ndjson = implode("\n", array_map(
            fn (array $record) => json_encode($record, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            $sanitized
        ));

        if ($ndjson !== '' && ! str_ends_with($ndjson, "\n")) {
            $ndjson .= "\n";
        }

        $namespace = rawurlencode($this->namespace());
        $url = rtrim($this->host(), '/').'/records/namespaces/'.$namespace.'/upsert';
        $response = $this->http->timeout(120)
            ->withHeaders($this->headers())
            ->withBody($ndjson, 'application/x-ndjson')
            ->post($url);

        $this->throwWithContext($response, $url, ['ndjson' => $ndjson]);

        return $response;
    }

    public function deleteRecord(string $id): void
    {
        if (! $this->isConfigured()) {
            Log::debug('Pinecone not configured; skipping deleteRecord.');

            return;
        }

        $url = rtrim($this->host(), '/').'/vectors/delete';
        $payload = [
            'ids' => [$id],
            'namespace' => $this->namespace(),
        ];

        $response = $this->http->timeout(120)
            ->withHeaders($this->headers())
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        $this->throwWithContext($response, $url, $payload);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function searchRecords(string $queryText, int $topK = 10, array $extra = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Pinecone is not configured. Please set PINECONE_API_KEY and PINECONE_INDEX_HOST.');
        }

        $namespace = rawurlencode($this->namespace());
        $url = rtrim($this->host(), '/').'/records/namespaces/'.$namespace.'/search';
        $body = [
            'query' => [
                'inputs' => [
                    'text' => $queryText,
                ],
                'top_k' => $topK,
            ],
        ];

        if (array_key_exists('filter', $extra)) {
            $body['query']['filter'] = $extra['filter'];
            unset($extra['filter']);
        }

        if (array_key_exists('fields', $extra)) {
            $body['fields'] = $extra['fields'];
            unset($extra['fields']);
        }

        $body = array_merge($body, $extra);

        $response = $this->http->timeout(120)
            ->withHeaders($this->headers())
            ->acceptJson()
            ->asJson()
            ->post($url, $body);

        $this->throwWithContext($response, $url, $body);

        return $response->json();
    }

    private function host(): string
    {
        $host = (string) config('services.pinecone.index_host');

        if ($host === '') {
            throw new RuntimeException('Pinecone is not configured. Please set PINECONE_INDEX_HOST.');
        }

        return $host;
    }

    private function headers(): array
    {
        $apiKey = (string) config('services.pinecone.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Pinecone is not configured. Please set PINECONE_API_KEY.');
        }

        return [
            'Api-Key' => $apiKey,
            'X-Pinecone-Api-Version' => (string) config('services.pinecone.api_version', '2025-10'),
            'Accept' => 'application/json',
        ];
    }

    private function throwWithContext($response, string $url, array $payload): void
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            Log::error('Pinecone request failed', [
                'url' => $url,
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw $exception;
        }
    }

    private function namespace(): string
    {
        return (string) config('services.pinecone.namespace', 'packages');
    }

    private function sanitizeRecord(array $record): array
    {
        $sanitized = [];

        foreach ($record as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }

            if (is_array($value)) {
                $value = array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));

                $value = array_map(function ($item) {
                    if (is_bool($item)) {
                        return $item ? 'true' : 'false';
                    }

                    if (is_int($item) || is_float($item)) {
                        return (string) $item;
                    }

                    return trim((string) $item);
                }, $value);

                $value = array_values(array_filter($value, fn ($item) => $item !== ''));

                if ($value === []) {
                    continue;
                }
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
