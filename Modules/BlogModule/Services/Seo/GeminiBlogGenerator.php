<?php

namespace Modules\BlogModule\Services\Seo;

use Modules\BlogModule\Entities\Article;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiBlogGenerator
{
    private ?string $lastImageGenerationError = null;

    private ?string $sceneAspectRatioOverride = null;

    public function getLastImageGenerationError(): ?string
    {
        return $this->lastImageGenerationError;
    }

    private function noteImageGenerationError(string $message): void
    {
        $this->lastImageGenerationError = Str::limit($message, 500);
    }

    /**
     * Generate SEO meta fields for a service without creating a blog article.
     *
     * @return array{meta_title_ar:string,meta_title_en:string,meta_description_ar:string,meta_description_en:string}
     */
    public function generateServiceSeoMeta(Service $service, string $extraPrompt = ''): array
    {
        return $this->generateSeoMetaPayload([
            'seo_source' => 'entity_profile',
            'entity_type' => 'service',
            'name_ar' => \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'ar') ?: 'الخدمة',
            'name_en' => \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'en') ?: 'service',
            'description_ar' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'ar'),
            'description_en' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'en'),
            'slug' => $service->slug,
            'category_name_ar' => \Modules\BlogModule\Support\BlogLocale::of($service->category, 'name', 'ar'),
            'category_name_en' => \Modules\BlogModule\Support\BlogLocale::of($service->category, 'name', 'en'),
            'category_slug' => $service->category?->slug,
            'client_extra_prompt' => $extraPrompt,
        ]);
    }

    /**
     * Generate SEO meta fields for a service using article content as the primary source.
     *
     * @return array{meta_title_ar:string,meta_title_en:string,meta_description_ar:string,meta_description_en:string}
     */
    public function generateServiceSeoMetaFromArticle(Service $service, Article $article, string $extraPrompt = ''): array
    {
        return $this->generateSeoMetaPayload([
            'seo_source' => 'latest_article',
            'entity_type' => 'service',
            'name_ar' => \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'ar') ?: 'الخدمة',
            'name_en' => \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'en') ?: 'service',
            'description_ar' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'ar'),
            'description_en' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'en'),
            'slug' => $service->slug,
            'category_name_ar' => \Modules\BlogModule\Support\BlogLocale::of($service->category, 'name', 'ar'),
            'category_name_en' => \Modules\BlogModule\Support\BlogLocale::of($service->category, 'name', 'en'),
            'article_title_ar' => $article->localeField('title', 'ar'),
            'article_title_en' => $article->localeField('title', 'en'),
            'article_excerpt_ar' => $article->localeField('excerpt', 'ar'),
            'article_excerpt_en' => $article->localeField('excerpt', 'en'),
            'article_body_ar' => Str::limit(strip_tags((string) $article->localeField('body', 'ar')), 1500),
            'article_body_en' => Str::limit(strip_tags((string) $article->localeField('body', 'en')), 1500),
            'client_extra_prompt' => $extraPrompt,
        ]);
    }

    /**
     * Generate SEO meta fields for a category (parent or child) without creating a blog article.
     *
     * @return array{meta_title_ar:string,meta_title_en:string,meta_description_ar:string,meta_description_en:string}
     */
    public function generateCategorySeoMeta(Category $category, string $extraPrompt = ''): array
    {
        return $this->generateSeoMetaPayload([
            'seo_source' => 'entity_profile',
            'entity_type' => $category->parent_id ? 'subcategory' : 'category',
            'name_ar' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'ar') ?: 'القسم',
            'name_en' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'en') ?: 'category',
            'description_ar' => \Modules\BlogModule\Support\BlogLocale::of($category, 'description', 'ar'),
            'description_en' => \Modules\BlogModule\Support\BlogLocale::of($category, 'description', 'en'),
            'slug' => $category->slug,
            'parent_name_ar' => \Modules\BlogModule\Support\BlogLocale::of($category->parent, 'name', 'ar'),
            'parent_name_en' => \Modules\BlogModule\Support\BlogLocale::of($category->parent, 'name', 'en'),
            'parent_slug' => $category->parent?->slug,
            'client_extra_prompt' => $extraPrompt,
        ]);
    }

    /**
     * Generate SEO meta fields for a category/subcategory using article content as the primary source.
     *
     * @return array{meta_title_ar:string,meta_title_en:string,meta_description_ar:string,meta_description_en:string}
     */
    public function generateCategorySeoMetaFromArticle(Category $category, Article $article, string $extraPrompt = ''): array
    {
        return $this->generateSeoMetaPayload([
            'seo_source' => 'latest_article',
            'entity_type' => $category->parent_id ? 'subcategory' : 'category',
            'name_ar' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'ar') ?: 'القسم',
            'name_en' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'en') ?: 'category',
            'description_ar' => \Modules\BlogModule\Support\BlogLocale::of($category, 'description', 'ar'),
            'description_en' => \Modules\BlogModule\Support\BlogLocale::of($category, 'description', 'en'),
            'slug' => $category->slug,
            'parent_name_ar' => \Modules\BlogModule\Support\BlogLocale::of($category->parent, 'name', 'ar'),
            'parent_name_en' => \Modules\BlogModule\Support\BlogLocale::of($category->parent, 'name', 'en'),
            'article_title_ar' => $article->localeField('title', 'ar'),
            'article_title_en' => $article->localeField('title', 'en'),
            'article_excerpt_ar' => $article->localeField('excerpt', 'ar'),
            'article_excerpt_en' => $article->localeField('excerpt', 'en'),
            'article_body_ar' => Str::limit(strip_tags((string) $article->localeField('body', 'ar')), 1500),
            'article_body_en' => Str::limit(strip_tags((string) $article->localeField('body', 'en')), 1500),
            'client_extra_prompt' => $extraPrompt,
        ]);
    }

    /**
     * Scene text for regenerating a blog featured image without the full article JSON.
     * The Gemini flash image pipeline adds the Arabic marketing panel (same as new AI articles).
     */
    public function buildRepairFeaturedImageScenePrompt(Service $service, ?Article $article = null): string
    {
        $service->loadMissing('category');
        $serviceNameAr = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'ar') ?: 'الخدمة';
        $serviceNameEn = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'en') ?: 'service';
        $brandHex = $this->normalizedBrandPrimaryHex();

        $topicHint = $serviceNameAr;
        if ($article !== null) {
            $titleAr = trim(strip_tags((string) $article->localeField('title', 'ar')));
            if ($titleAr !== '') {
                $topicHint = Str::limit($titleAr, 140, '');
            }
        }

        return 'Marketing hero scene for GCC home services. Strictly about '
            ."{$serviceNameEn} / {$serviceNameAr} only. "
            .'One professional MALE technician only, slightly zoomed out to show upper body and context. '
            ."Technician actively repairing equipment tied only to {$serviceNameAr}; visual topic must match: {$topicHint}. "
            .'Clean uniform; English word CLEAN365 visible as shirt print. '
            .'No text overlays in the photo area (no captions, no billboards). '
            ."Premium photoreal advertising; teal accent lighting; brand palette {$brandHex}; navy uniform; modern luxury home interior.";
    }

    /**
     * Scene prompt for service catalog images: photoreal technician + service context, no ad typography.
     */
    public function buildServiceCatalogImageScenePrompt(Service $service): string
    {
        $service->loadMissing('category');
        $serviceNameAr = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'ar') ?: 'الخدمة';
        $serviceNameEn = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'en') ?: 'service';
        $descriptionAr = Str::limit(
            strip_tags(\Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'ar') ?: ''),
            280,
            ''
        );
        $categoryAr = \Modules\BlogModule\Support\BlogLocale::of($service->category, 'name', 'ar');
        $brandHex = $this->normalizedBrandPrimaryHex();

        $context = $descriptionAr !== '' ? "Service details: {$descriptionAr}. " : '';
        if ($categoryAr) {
            $context .= "Category: {$categoryAr}. ";
        }

        $aspect = $this->serviceCatalogAspectRatio();

        return "Wide {$aspect} catalog hero photo for a mobile app service card (full-bleed landscape). "
            ."Service name: {$serviceNameEn} / {$serviceNameAr}. "
            .$context
            .'Show one professional MALE technician actively performing THIS exact service only '
            ."(visual must match {$serviceNameAr} — e.g. AC repair shows technician repairing an air conditioner). "
            .$this->serviceCatalogFramingDirectives()
            .'Sharp focus, bright even lighting, high clarity — suitable for a large card thumbnail. '
            .'Clean uniform; English word CLEAN365 visible as shirt print only. '
            .'Absolutely NO text overlays, headlines, buttons, phone numbers, captions, watermarks, or graphic design panels. '
            ."Premium photoreal advertising photography; Clean365 teal brand palette {$brandHex}; "
            .'navy blue uniform polo and cap, teal microfiber cloth and tool accents, bright modern Saudi home interior.';
    }

    /**
     * Generic JSON helper for Phase 0.5 SEO commands. Sends a structured
     * prompt to Gemini, falls back across configured base URLs and models,
     * and returns the parsed JSON payload (or null on a non-recoverable
     * 400/404). Throws when every endpoint fails so callers can log + skip.
     *
     * @param  array<string, mixed>  $expectedKeys  Used only for richer error logs.
     * @return array<string, mixed>
     */
    public function callJson(string $prompt, array $expectedKeys = []): array
    {
        $response = $this->invokeGeminiText(
            $prompt,
            $this->seoModelsToTry(),
            $this->seoGenerationConfig(),
            'Gemini text models not available',
            forSeo: true,
        );

        $text = $this->extractCandidateText($response);
        $payload = $this->decodeBlogJsonPayload($text);

        if (! is_array($payload)) {
            $hint = $expectedKeys !== []
                ? ' Expected keys: '.implode(',', $expectedKeys).'.'
                : '';

            throw new \RuntimeException(
                'Gemini response is not a valid JSON payload.'.$hint
                .' Snippet: '.Str::limit(preg_replace('/\s+/', ' ', $text) ?? '', 400)
            );
        }

        return $payload;
    }

    /**
     * Generate a scene-only service catalog image (no Arabic ad panel).
     *
     * @return string|null Local temp path to decoded image
     */
    public function createServiceCatalogImage(Service $service, ?string $traceId = null): ?string
    {
        $prompt = $this->buildServiceCatalogImageScenePrompt($service);

        return $this->createImageFromPrompt($prompt, $traceId, sceneOnly: true, aspectRatio: $this->serviceCatalogAspectRatio());
    }

    /**
     * Scene prompt for category catalog icons: photoreal, brand colors, no typography.
     */
    public function buildCategoryCatalogImageScenePrompt(Category $category): string
    {
        $nameAr = \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'ar') ?: 'التصنيف';
        $nameEn = \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'en') ?: 'category';
        $descriptionAr = Str::limit(
            strip_tags(\Modules\BlogModule\Support\BlogLocale::of($category, 'description', 'ar') ?: ''),
            280,
            ''
        );
        $brandHex = $this->normalizedBrandPrimaryHex();
        $context = $descriptionAr !== '' ? "Category details: {$descriptionAr}. " : '';

        return "Square 1:1 catalog icon photo for a mobile app category card. "
            ."Category name: {$nameEn} / {$nameAr}. "
            .$context
            .'Show one professional MALE Clean365 technician scene that visually represents THIS category only '
            ."(tools, workspace, and activity must match {$nameAr}). "
            .$this->serviceCatalogFramingDirectives()
            .'Sharp focus, bright even lighting, high clarity — suitable as a category thumbnail. '
            .'Clean uniform; English word CLEAN365 visible as shirt print only. '
            .'Absolutely NO text overlays, headlines, buttons, phone numbers, captions, watermarks, logos, or graphic design panels. '
            ."Premium photoreal advertising photography; Clean365 teal brand palette {$brandHex}; "
            .'navy blue uniform polo and cap, teal microfiber cloth and tool accents, bright modern Saudi home interior.';
    }

    /**
     * Generate a scene-only category catalog image (no marketing typography).
     *
     * @return string|null Local temp path to decoded image
     */
    public function createCategoryCatalogImage(Category $category, ?string $traceId = null): ?string
    {
        $prompt = $this->buildCategoryCatalogImageScenePrompt($category);

        return $this->createImageFromPrompt($prompt, $traceId, sceneOnly: true, aspectRatio: '1:1');
    }

    public function generate(Service $service, string $extraPrompt = '', ?string $traceId = null): array
    {
        $relatedArticles = Article::published()
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(3)
            ->get(['title', 'slug']);

        $internalLinks = $relatedArticles
            ->map(function (Article $article) {
                return [
                    'title_ar' => $article->localeField('title', 'ar'),
                    'title_en' => $article->localeField('title', 'en'),
                    'slug' => $article->slug,
                ];
            })
            ->values()
            ->all();

        $prompt = $this->buildPrompt($service, $internalLinks, $extraPrompt);

        $response = $this->invokeGeminiText(
            $prompt,
            $this->textModelsToTry(),
            $this->generationConfig(),
            'Gemini text models not available'
        );

        $text = $this->extractCandidateText($response);
        $finishReason = (string) data_get($response, 'candidates.0.finishReason', '');

        if ($finishReason === 'MAX_TOKENS') {
            Log::warning('Gemini finished with MAX_TOKENS; JSON may be truncated. Raise GEMINI_MAX_OUTPUT_TOKENS or shorten the prompt output.', [
                'finishReason' => $finishReason,
                'trace_id' => $traceId,
            ]);
        }

        $payload = $this->decodeBlogJsonPayload($text);

        if (! is_array($payload)) {
            $hint = $finishReason === 'MAX_TOKENS'
                ? ' (likely output token limit; set GEMINI_MAX_OUTPUT_TOKENS higher, e.g. 32768, and ensure meta_description fields stay empty in the model output.)'
                : '';

            throw new \RuntimeException(
                'Gemini response is not a valid JSON payload.'.$hint
                .' Snippet: '.Str::limit(preg_replace('/\s+/', ' ', $text) ?? '', 400)
            );
        }

        return $payload;
    }

    public function createImageFromPrompt(string $imagePrompt, ?string $traceId = null, bool $sceneOnly = false, ?string $aspectRatio = null): ?string
    {
        $previousAspect = $this->sceneAspectRatioOverride;
        if ($aspectRatio !== null && $aspectRatio !== '') {
            $this->sceneAspectRatioOverride = $aspectRatio;
        }

        try {
            return $this->createImageFromPromptInner($imagePrompt, $traceId, $sceneOnly);
        } finally {
            $this->sceneAspectRatioOverride = $previousAspect;
        }
    }

    private function createImageFromPromptInner(string $imagePrompt, ?string $traceId, bool $sceneOnly): ?string
    {
        $this->lastImageGenerationError = null;

        $parts = $sceneOnly
            ? $this->serviceCatalogImagePromptParts($imagePrompt)
            : $this->enhanceImagePromptParts($imagePrompt);
        $geminiOnly = (bool) config('services.image_generation.gemini_only', false);
        $preferredProvider = strtolower((string) config('services.image_generation.provider', 'gemini'));
        $combinedPrompt = $this->combinePromptAndNegativeForPollinations($parts);

        // When IMAGE_PROVIDER=openai, try OpenAI first before Gemini
        if ($preferredProvider === 'openai') {
            $openaiKey = (string) config('services.image_generation.openai.api_key');
            if ($openaiKey !== '') {
                $openaiImage = $this->createImageFromOpenAI($combinedPrompt, $traceId);
                if ($openaiImage) {
                    Log::info('Image generation succeeded with OpenAI provider (preferred)', [
                        'provider' => 'openai',
                        'trace_id' => $traceId,
                    ]);

                    return $openaiImage;
                }
                Log::warning('Image generation failed with OpenAI provider (preferred), falling back to Gemini', [
                    'provider' => 'openai',
                    'trace_id' => $traceId,
                ]);
            } else {
                Log::info('Image generation: OpenAI preferred but API key is missing, falling back to Gemini', [
                    'trace_id' => $traceId,
                ]);
            }
        }

        $geminiImage = $this->createImageFromGemini($parts, $traceId, $sceneOnly);
        if ($geminiImage) {
            Log::info('Image generation succeeded with Gemini provider', [
                'provider' => 'gemini',
                'trace_id' => $traceId,
            ]);

            return $geminiImage;
        }

        Log::warning('Image generation failed or unavailable with Gemini provider', [
            'provider' => 'gemini',
            'gemini_only' => $geminiOnly,
            'scene_only' => $sceneOnly,
            'trace_id' => $traceId,
        ]);

        if ($geminiOnly) {
            Log::info('Image generation: skipping OpenAI/Pollinations (gemini_only mode)', [
                'trace_id' => $traceId,
            ]);

            return null;
        }

        // Service catalog: never use Pollinations (long Arabic prompts in GET URLs often time out).
        if ($sceneOnly) {
            $openaiKey = (string) config('services.image_generation.openai.api_key');
            if ($openaiKey !== '') {
                $openaiPrompt = $parts['raw'].' '.$this->serviceCatalogFramingDirectives()
                    .' '.$this->highFidelityImageDirectives();
                $openaiImage = $this->createImageFromOpenAI($openaiPrompt, $traceId);
                if ($openaiImage) {
                    Log::info('Image generation succeeded with OpenAI provider (service catalog fallback)', [
                        'provider' => 'openai',
                        'trace_id' => $traceId,
                    ]);

                    return $openaiImage;
                }
                Log::warning('Image generation failed with OpenAI provider (service catalog fallback)', [
                    'provider' => 'openai',
                    'trace_id' => $traceId,
                ]);
            }

            return null;
        }

        $openaiKey = (string) config('services.image_generation.openai.api_key');
        if ($preferredProvider !== 'openai' && $openaiKey !== '') {
            $openaiImage = $this->createImageFromOpenAI($combinedPrompt, $traceId);
            if ($openaiImage) {
                Log::info('Image generation succeeded with OpenAI provider', [
                    'provider' => 'openai',
                    'trace_id' => $traceId,
                ]);

                return $openaiImage;
            }
            Log::warning('Image generation failed with OpenAI provider', [
                'provider' => 'openai',
                'trace_id' => $traceId,
            ]);
        } elseif ($preferredProvider !== 'openai') {
            Log::info('Image generation skipped for OpenAI provider because API key is missing', [
                'provider' => 'openai',
                'trace_id' => $traceId,
            ]);
        }

        $pollinationsImage = $this->createImageFromPollinations($combinedPrompt, $traceId);
        if ($pollinationsImage) {
            Log::info('Image generation succeeded with Pollinations provider', [
                'provider' => 'pollinations',
                'trace_id' => $traceId,
            ]);

            return $pollinationsImage;
        }

        $this->noteImageGenerationError('All image providers failed (Gemini, OpenAI, Pollinations).');

        Log::warning('Image generation failed with all providers', [
            'providers_tried' => ['gemini', 'openai', 'pollinations'],
            'trace_id' => $traceId,
        ]);

        return null;
    }

    /**
     * @return list<string>
     */
    private function geminiImageModelCandidates(): array
    {
        $models = config('services.gemini.image_models', []);
        if (! is_array($models)) {
            $models = [];
        }

        $primary = $this->normalizeModelId((string) config('services.gemini.image_model', ''));
        if ($primary !== '') {
            array_unshift($models, $primary);
        }

        $models = array_values(array_unique(array_filter(array_map(
            fn ($m) => $this->normalizeModelId((string) $m),
            $models
        ))));

        if ($models === []) {
            $models = ['gemini-3.1-flash-image'];
        }

        return $models;
    }

    /**
     * Gemini native image generation (Vertex AI Platform or AI Studio). Tries streamGenerateContent then generateContent.
     *
     * @param  array{raw: string, prompt: string, negative: string}  $parts
     * @return string|null Local temp path to decoded image, or null on failure
     */
    private function vertexClient(): GeminiVertexClient
    {
        return app(GeminiVertexClient::class);
    }

    private function geminiImageApiKey(): string
    {
        return $this->vertexClient()->resolveImageApiKey();
    }

    private function geminiImageUsesAiPlatform(): bool
    {
        return $this->vertexClient()->imageUsesAiPlatform();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGeminiImageRequestBody(string $userText, bool $sceneOnly): array
    {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $userText],
                ],
            ],
        ];

        if ($this->geminiImageUsesAiPlatform()) {
            return ['contents' => $contents];
        }

        return [
            'contents' => $contents,
            'generationConfig' => [
                'responseModalities' => ['IMAGE', 'TEXT'],
                'imageConfig' => [
                    'aspectRatio' => $sceneOnly ? $this->serviceCatalogAspectRatio() : '3:2',
                ],
            ],
        ];
    }

    private function geminiImageRequestUrl(string $model, string $endpoint, string $apiKey): string
    {
        return $this->vertexClient()->buildImageRequestUrl($model, $endpoint, $apiKey);
    }

    private function createImageFromGemini(array $parts, ?string $traceId = null, bool $sceneOnly = false): ?string
    {
        $apiKey = $this->geminiImageApiKey();
        if ($apiKey === '') {
            $this->noteImageGenerationError('GEMINI_IMAGE_API_KEY (or GEMINI_API_KEY) is missing in .env');

            return null;
        }

        $userText = $sceneOnly
            ? $this->buildGeminiFlashSceneOnlyImageUserText($parts)
            : $this->buildGeminiFlashImageUserText($parts);
        if (strlen($userText) > 32000) {
            $userText = substr($userText, 0, 31997).'...';
        }

        $body = $this->buildGeminiImageRequestBody($userText, $sceneOnly);

        $timeout = max(60, (int) config('services.image_generation.gemini_image_timeout', 240));
        $maxAttempts = $sceneOnly ? 3 : 2;
        $errors = [];
        $endpoints = $this->geminiImageUsesAiPlatform()
            ? ['streamGenerateContent', 'generateContent']
            : ['streamGenerateContent', 'generateContent'];

        foreach ($this->geminiImageModelCandidates() as $model) {
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                foreach ($endpoints as $endpoint) {
                    $path = $this->requestGeminiImage(
                        $model,
                        $endpoint,
                        $body,
                        $apiKey,
                        $timeout,
                        $traceId
                    );
                    if ($path !== null) {
                        return $path;
                    }
                }

                $errors[] = "{$model} attempt {$attempt}";
                usleep(random_int(1_500_000, 3_000_000));
            }
        }

        $this->noteImageGenerationError(
            $this->lastImageGenerationError
            ?? ('Gemini image failed for models: '.implode(', ', array_unique($errors)))
        );

        return null;
    }

    /**
     * @return string|null Temp image path
     */
    private function requestGeminiImage(
        string $model,
        string $endpoint,
        array $body,
        string $apiKey,
        int $timeout,
        ?string $traceId,
    ): ?string {
        $url = $this->geminiImageRequestUrl($model, $endpoint, $apiKey);

        try {
            $response = Http::acceptJson()->timeout($timeout)->post($url, $body);

            if (! $response->successful()) {
                $status = $response->status();
                $snippet = Str::limit((string) $response->body(), 300);
                $this->noteImageGenerationError("Gemini {$endpoint} HTTP {$status} ({$model}): {$snippet}");

                Log::warning("Gemini image {$endpoint} failed", [
                    'model' => $model,
                    'status' => $status,
                    'snippet' => $snippet,
                    'trace_id' => $traceId,
                ]);

                return null;
            }

            $raw = (string) $response->body();
            $chunks = $this->parseGeminiStreamResponseChunks($raw);
            $binary = $this->firstInlineImageBinaryFromChunks($chunks);

            if ($binary !== null) {
                $ext = str_starts_with($binary, "\x89PNG") ? 'png'
                    : (str_starts_with($binary, "\xff\xd8\xff") ? 'jpg' : 'jpg');

                return $this->writeTempImage($binary, $ext);
            }

            $hint = $this->geminiImageResponseHint($chunks);
            $this->noteImageGenerationError(
                "Gemini {$endpoint} returned no image ({$model})".($hint ? ": {$hint}" : '')
            );

            Log::warning("Gemini image {$endpoint} returned no inline image", [
                'model' => $model,
                'hint' => $hint,
                'snippet' => Str::limit($raw, 400),
                'trace_id' => $traceId,
            ]);
        } catch (\Throwable $e) {
            $this->noteImageGenerationError("Gemini {$endpoint} exception ({$model}): ".$e->getMessage());

            Log::warning("Gemini image {$endpoint} exception", [
                'model' => $model,
                'message' => $e->getMessage(),
                'trace_id' => $traceId,
            ]);
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    private function geminiImageResponseHint(array $chunks): ?string
    {
        foreach ($chunks as $chunk) {
            $block = data_get($chunk, 'promptFeedback.blockReason');
            if (is_string($block) && $block !== '') {
                return 'blocked: '.$block;
            }

            $reason = data_get($chunk, 'candidates.0.finishReason');
            if (is_string($reason) && $reason !== '' && $reason !== 'STOP') {
                return 'finish: '.$reason;
            }
        }

        return null;
    }

    /**
     * Single-block prompt: split hero + Arabic typography panel (image models often skip text if layout is vague
     * or if "random text" appears in negatives — see geminiImageNegative()).
     *
     * @param  array{raw: string, prompt: string, negative: string}  $parts
     */
    private function buildGeminiFlashImageUserText(array $parts): string
    {
        $hex = $this->normalizedBrandPrimaryHex();
        $rawScene = trim((string) ($parts['raw'] ?? $parts['prompt']));
        $phone = trim((string) config('services.gemini.image_ad_phone', '+966582250326'));
        if ($phone === '') {
            $phone = '+966582250326';
        }

        $rawScene = preg_replace('/\s+/u', ' ', $rawScene) ?? $rawScene;
        $avoid = $this->geminiImageNegative();

        $navy = $this->normalizedBrandSecondaryHex();
        $headlineInstruction = 'Large main headline in Arabic (RTL), bold modern Arabic font, white with soft teal accent, '
            .'perfect spelling and alignment, must describe the SAME service as the technician scene (same appliance/trade as above). '
            .'Example length/style only (adapt words to match the scene): "تصليح ثلاجات سريع وموثوق" for refrigerator repair.';

        $sub = 'فنيين محترفين • خدمة في نفس اليوم • ضمان على الخدمة';
        $cta = 'احجز خدمتك الآن';

        return <<<PROMPT
Design a single finished advertisement image (not a plain photo). Layout is mandatory:

Overall format: wide horizontal LANDSCAPE banner, aspect ratio 3:2, suitable for a blog listing and article hero (not square, not portrait). Fill the full frame edge to edge.

LEFT approximately 65 percent of the frame: cinematic photoreal hero — Clean365 home cleaning commercial.
{$rawScene}
One professional male cleaner or technician in navy blue uniform polo and cap with English "CLEAN365" on the chest, black nitrile gloves, actively cleaning or servicing equipment that matches this service. Modern luxury home interior with white marble surfaces, warm natural lighting, teal microfiber cloth and spray bottle accents, brand teal {$hex}, ultra realistic, 8k, depth of field, volumetric light, shot on Sony a7R IV 50mm, premium advertising photography.

RIGHT approximately 35 percent of the frame: a solid vertical panel with dark navy gradient {$navy} (no busy photo behind text). This panel MUST contain clearly painted, high-contrast advertisement typography — all of the following must appear as real text in the image (not empty, not implied):

1) HEADLINE — {$headlineInstruction}

2) SUBHEADLINE — smaller white Arabic below, exact line (copy exactly):
{$sub}

3) CALL TO ACTION — rounded teal button or pill shape (hex {$hex}) with white Arabic text, exact phrase (copy exactly):
{$cta}

4) PHONE — below the button, white Arabic numerals and plus sign, exact string (copy exactly):
{$phone}

Typography rules: crisp vector-like edges, RTL where appropriate, no gibberish, no missing letters, no Latin substitute for Arabic. The Arabic copy above is required on-image; do not output an image with only the photo and no text panel.

Avoid: {$avoid}.
PROMPT;
    }

    /**
     * Full-frame photoreal scene for service catalog (no marketing typography panel).
     *
     * @param  array{raw: string, prompt: string, negative: string}  $parts
     */
    private function buildGeminiFlashSceneOnlyImageUserText(array $parts): string
    {
        $hex = $this->normalizedBrandPrimaryHex();
        $navy = $this->normalizedBrandSecondaryHex();
        $framing = $this->serviceCatalogFramingDirectives();
        $rawScene = trim((string) ($parts['raw'] ?? $parts['prompt']));
        $rawScene = preg_replace('/\s+/u', ' ', $rawScene) ?? $rawScene;
        $avoid = 'cartoon, illustration, painting, CGI render look, anime, distorted hands or face, blurry, low resolution, '
            . 'extreme close-up, tight crop, cropped head, cut-off head, cut-off legs, cut-off feet, face-only portrait, hands-only macro, subject touching frame edges, '
            . 'female technician, unrelated appliances or trade, random text, headlines, captions, buttons, phone numbers, '
            . 'watermarks, stock photo marks, marketing panels, Arabic or English typography overlays, empty studio backdrop';

        $aspect = $this->serviceCatalogAspectRatio();

        return <<<PROMPT
Create a single finished photoreal image — NOT a split marketing banner.

Format: wide horizontal LANDSCAPE, aspect ratio {$aspect}, edge to edge. Full frame is one cohesive scene with NO empty panels — mobile app service card hero.

Scene (must match the service name and description exactly):
{$rawScene}

Visual style: cinematic premium advertising photography for Clean365 home services in Saudi Arabia.
One professional male technician in clean uniform with English "CLEAN365" printed on the chest only.
{$framing}
Technician actively working on equipment that clearly matches the service (correct tools and appliance/trade); person + workspace + tools all readable in one wide shot.
Clean365 brand colors: navy blue uniform (polo shirt and cap, hex {$navy}), teal accents {$hex} on cleaning cloth, spray bottle, rim light and soft background highlights. Bright modern Saudi home with white marble and warm natural lighting. No purple, no violet, no mauve.
Modern luxury Saudi home, kitchen, or workspace. Ultra realistic, 8k, moderate depth of field, shot on Sony a7R IV 28mm wide lens.

STRICT: No text anywhere except the word CLEAN365 on the shirt. No headlines, subheadlines, CTA buttons, phone numbers, captions, logos, or watermarks.

Avoid: {$avoid}.
PROMPT;
    }

    /**
     * Pollinations negative list includes "random text" which suppresses designed ad copy for Gemini; use a stricter subset.
     */
    private function geminiImageNegative(): string
    {
        return 'cartoon, illustration, painting, CGI render look, anime, distorted hands or face, blurry, low resolution, '
            .'female technician, unrelated appliances, garbled or misspelled Arabic, extra headlines beyond the four specified blocks, '
            .'watermark, stock photo marks, empty right panel with no text, text only on shirt without the right panel layout';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseGeminiStreamResponseChunks(string $body): array
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
    private function firstInlineImageBinaryFromChunks(array $chunks): ?string
    {
        foreach ($chunks as $chunk) {
            $parts = data_get($chunk, 'candidates.0.content.parts', []);
            if (! is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (! is_array($part)) {
                    continue;
                }
                $b64 = data_get($part, 'inlineData.data') ?? data_get($part, 'inline_data.data');
                if (! is_string($b64) || $b64 === '') {
                    continue;
                }
                $binary = base64_decode($b64, true);
                if ($binary !== false && $binary !== '') {
                    return $binary;
                }
            }
        }

        return null;
    }

    /**
     * OpenAI Images API (DALL·E 3). API key: OPENAI_API_KEY.
     *
     * @return string|null Local temp path to downloaded image, or null on failure
     */
    private function createImageFromOpenAI(string $imagePrompt, ?string $traceId = null): ?string
    {
        $apiKey = (string) config('services.image_generation.openai.api_key');
        if ($apiKey === '') {
            return null;
        }

        $model = (string) config('services.image_generation.openai.model', 'gpt-image-1.5');
        $size = (string) config('services.image_generation.openai.size', '1024x1024');
        $quality = $this->normalizeOpenAiImageQuality((string) config('services.image_generation.openai.quality', 'high'));

        if (strlen($imagePrompt) > 4000) {
            $imagePrompt = substr($imagePrompt, 0, 3997).'...';
        }

        try {
            $maxAttempts = 3;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $model,
                        'prompt' => $imagePrompt,
                        'n' => 1,
                        'size' => $size,
                        'quality' => $quality,
                    ]);

                if ($response->successful()) {
                    $url = data_get($response->json(), 'data.0.url');

                    if (is_string($url) && $url !== '') {
                        $imageResponse = Http::timeout(90)
                            ->withHeaders(['User-Agent' => 'Clean365Backend/1.0'])
                            ->get($url);

                        if (! $imageResponse->successful()) {
                            Log::warning('OpenAI image URL download HTTP error', [
                                'status' => $imageResponse->status(),
                                'snippet' => Str::limit($imageResponse->body(), 200),
                                'trace_id' => $traceId,
                            ]);
                        }

                        $imageData = $imageResponse->body();
                        $binary = (string) $imageData;

                        if ($binary !== '' && strlen($binary) > 500) {
                            $ext = $this->detectBinaryImageExtension($binary);
                            if ($ext !== null) {
                                return $this->writeTempImage($binary, $ext);
                            }
                        }

                        Log::warning('OpenAI image URL returned empty or unrecognized image body', [
                            'body_length' => strlen($binary),
                            'trace_id' => $traceId,
                        ]);

                        return null;
                    }

                    $b64 = data_get($response->json(), 'data.0.b64_json');

                    if (is_string($b64) && $b64 !== '') {
                        $binary = base64_decode($b64, true);

                        if ($binary !== false && $binary !== '') {
                            $ext = $this->detectBinaryImageExtension($binary) ?? 'png';

                            return $this->writeTempImage($binary, $ext);
                        }
                    }

                    return null;
                }

                $status = $response->status();

                if (in_array($status, [429, 503, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                Log::warning('OpenAI images/generations failed', [
                    'status' => $response->status(),
                    'snippet' => Str::limit($response->body(), 300),
                    'trace_id' => $traceId,
                ]);

                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI image generation exception', [
                'message' => $e->getMessage(),
                'trace_id' => $traceId,
            ]);

            return null;
        }

        return null;
    }

    private function createImageFromPollinations(string $imagePrompt, ?string $traceId = null): ?string
    {
        $timeout = max(45, (int) config('services.image_generation.pollinations_timeout', 120));
        $maxUrlLength = 1800;
        if (strlen($imagePrompt) > 1200) {
            $imagePrompt = Str::limit($imagePrompt, 1200, '');
        }

        try {
            $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($imagePrompt);
            if (strlen($url) > $maxUrlLength) {
                Log::warning('Pollinations prompt URL too long; skipping', [
                    'url_length' => strlen($url),
                    'trace_id' => $traceId,
                ]);

                return null;
            }

            $response = Http::timeout($timeout)->get($url);
        } catch (\Throwable $e) {
            Log::warning('Pollinations image generation exception', [
                'message' => $e->getMessage(),
                'trace_id' => $traceId,
            ]);

            return null;
        }

        $imageData = $response->body();

        if (! $imageData) {
            Log::warning('Pollinations image request returned empty body', [
                'status' => $response->status(),
                'trace_id' => $traceId,
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Pollinations image request failed', [
                'status' => $response->status(),
                'snippet' => Str::limit($response->body(), 200),
                'trace_id' => $traceId,
            ]);

            return null;
        }

        $ext = $this->detectBinaryImageExtension($imageData);
        if ($ext === null) {
            Log::warning('Pollinations response was not a recognized image (wrong MIME/HTML?)', [
                'status' => $response->status(),
                'body_prefix' => Str::limit($imageData, 120),
                'trace_id' => $traceId,
            ]);

            return null;
        }

        return $this->writeTempImage($imageData, $ext);
    }

    /**
     * gpt-image / Images API accepts only: low, medium, high, auto (not DALL·E "standard"/"hd").
     */
    private function normalizeOpenAiImageQuality(string $raw): string
    {
        $q = strtolower(trim($raw));
        $allowed = ['low', 'medium', 'high', 'auto'];
        if (in_array($q, $allowed, true)) {
            return $q;
        }

        if (in_array($q, ['standard', 'hd', '1', '2'], true)) {
            return 'high';
        }

        return 'high';
    }

    /**
     * @return 'png'|'jpg'|'webp'|'gif'|null
     */
    private function detectBinaryImageExtension(string $binary): ?string
    {
        if ($binary === '') {
            return null;
        }

        if (str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
            return 'png';
        }

        if (str_starts_with($binary, "\xff\xd8\xff")) {
            return 'jpg';
        }

        if (strlen($binary) >= 12 && str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP') {
            return 'webp';
        }

        if (str_starts_with($binary, 'GIF87a') || str_starts_with($binary, 'GIF89a')) {
            return 'gif';
        }

        return null;
    }

    private function writeTempImage(string $imageData, string $extension = 'png'): ?string
    {
        $tmp = storage_path('app/tmp/'.Str::uuid().'.'.$extension);

        if (! is_dir(dirname($tmp))) {
            mkdir(dirname($tmp), 0755, true);
        }

        file_put_contents($tmp, $imageData);

        return $tmp;
    }

    /**
     * Shared base prompt: max quality + strict no-text + brand primary color.
     *
     * @return array{raw: string, prompt: string, negative: string}
     */
    /**
     * Service catalog prompts are already self-contained; avoid duplicating quality/brand blocks (hurts Gemini + URL fallbacks).
     *
     * @return array{raw: string, prompt: string, negative: string}
     */
    private function serviceCatalogImagePromptParts(string $prompt): array
    {
        $raw = trim($prompt);

        return [
            'raw' => $raw,
            'prompt' => $raw,
            'negative' => '',
        ];
    }

    private function enhanceImagePromptParts(string $prompt): array
    {
        $hex = $this->normalizedBrandPrimaryHex();
        $navy = $this->normalizedBrandSecondaryHex();

        $quality = 'Ultra photorealistic premium advertising photography, highest quality, ultra-detailed, sharp focus, true-to-life proportions, realistic hands and face, high dynamic range, bright natural indoor lighting, clean composition.';
        $hexDigits = strtoupper(ltrim($hex, '#'));
        $navyDigits = strtoupper(ltrim($navy, '#'));
        $brand = "Clean365 brand palette: primary teal exactly hex {$hex} (Color(0xFF{$hexDigits})) for cleaning cloth, tool accents, rim light and CTA highlights; secondary navy exactly hex {$navy} (Color(0xFF{$navyDigits})) for uniform polo shirt and cap. Bright modern home interior, white marble surfaces. Never use purple, violet, or mauve.";

        $negative = 'cartoon, illustration, painting, CGI, 3D render, anime, distorted anatomy, deformed hands, blurry, low quality, low resolution, female person, unrelated objects, unrelated service scene, random text, random letters, random words, watermark, logo';

        $raw = trim($prompt);

        return [
            'raw' => $raw,
            'prompt' => $raw.' '.$quality.' '.$brand,
            'negative' => $negative,
        ];
    }

    /**
     * Reinforces photorealism + exact brand hex with no text overlays.
     */
    private function highFidelityImageDirectives(): string
    {
        $hex = $this->normalizedBrandPrimaryHex();

        $navy = $this->normalizedBrandSecondaryHex();

        return "Real DSLR-style commercial shot, highest fidelity, strictly relevant to the requested service only. Show one male cleaner or technician in navy uniform ({$navy}) with teal accents ({$hex}) on cleaning cloth and tools. Bright modern Saudi home, warm natural lighting. Only allowed text is CLEAN365 on the shirt; no other text, letters, logos, labels, captions, or watermarks. No purple or violet tones.";
    }

    private function serviceCatalogAspectRatio(): string
    {
        if ($this->sceneAspectRatioOverride !== null && $this->sceneAspectRatioOverride !== '') {
            $override = trim($this->sceneAspectRatioOverride);
            if (in_array($override, ['1:1', '16:9', '21:9', '3:2', '4:3', '3:4', '4:5', '9:16'], true)) {
                return $override;
            }
        }

        $ratio = trim((string) config('services.image_generation.service_catalog_aspect_ratio', '4:3'));

        return in_array($ratio, ['1:1', '16:9', '21:9', '3:2', '4:3'], true) ? $ratio : '4:3';
    }

    private function serviceCatalogFramingDirectives(): string
    {
        return 'MANDATORY FRAMING — distant environmental wide shot (NOT portrait, NOT close-up). '
            . 'Camera 5–6 meters away on a 28mm lens. '
            . 'The entire technician must be visible with SAFE MARGINS: at least 12% empty space above the head, 12% below feet/knees, 10% on left and right — subject must NOT touch any edge. '
            . 'Full head, face, torso, arms, hands, legs, and feet (or complete kneeling pose with both knees and feet visible). '
            . 'Technician height is at most 50% of the image; centered with room around them. '
            . 'Waist-up only, tight crop, macro hands, or face filling the frame are WRONG. ';
    }

    private function normalizedBrandPrimaryHex(): string
    {
        $raw = trim((string) config('services.image_generation.brand_primary_hex', '#008080'));
        if ($raw === '') {
            return '#008080';
        }

        if ($raw[0] !== '#') {
            $raw = '#'.ltrim($raw, '#');
        }

        return $raw;
    }

    private function normalizedBrandSecondaryHex(): string
    {
        $raw = trim((string) config('services.image_generation.brand_secondary_hex', '#1a2b48'));
        if ($raw === '') {
            return '#1a2b48';
        }

        if ($raw[0] !== '#') {
            $raw = '#'.ltrim($raw, '#');
        }

        return $raw;
    }

    /**
     * Pollinations: single prompt string (negative instructions inlined).
     *
     * @param  array{raw?: string, prompt: string, negative: string}  $parts
     */
    private function combinePromptAndNegativeForPollinations(array $parts): string
    {
        return $parts['prompt'].' '.$this->highFidelityImageDirectives()
            .' No cartoon, no illustration, no painting, no CGI, no 3D render, no anime, no distorted anatomy, no deformed hands, no blurry, no low quality, no wrong brand colors, no text, no letters, no words, no watermark.';
    }

    private function buildPrompt(Service $service, array $internalLinks, string $extraPrompt): string
    {
        $category = $service->category;
        $serviceNameAr = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'ar') ?: 'الخدمة';
        $serviceNameEn = \Modules\BlogModule\Support\BlogLocale::of($service, 'name', 'en') ?: 'service';
        $brandHex = $this->normalizedBrandPrimaryHex();

        return "You are an SEO Arabic/English blog generator.\n"
            ."Return only valid JSON without markdown fences.\n"
            ."JSON keys exactly:\n"
            ."title_ar,title_en,excerpt_ar,excerpt_en,body_ar,body_en,meta_title_ar,meta_title_en,meta_description_ar,meta_description_en,keywords_ar,keywords_en,slug,image_prompt\n"
            ."keywords_ar and keywords_en: arrays of at most 8 short strings each.\n"
            ."excerpt_ar and excerpt_en must be plain text only: no HTML tags, no markdown, single short summary paragraph each (under 400 characters each).\n"
            ."meta_description_ar and meta_description_en must be exactly \"\" (empty strings). The server fills SEO description from body; do not write any text in these two fields.\n"
            ."body_ar and body_en must be clean semantic HTML (no markdown) using only: h2,h3,p,strong,ul,ol,li,a.\n"
            ."For body_ar, keep RTL-friendly sectioning and bold key phrases with <strong>.\n"
            ."Use this article structure for body_ar EXACTLY and in the same order with numbered sections and service points.\n"
            ."Match this editorial style closely: practical, promotional, clear Arabic, and easy to scan.\n"
            ."CRITICAL TOPIC RULE: The article MUST stay strictly about the provided service and category only.\n"
            ."Never switch to another domain (for example: do not talk about AC maintenance unless the service itself is AC-related).\n"
            ."Use {$serviceNameAr} as the central topic in all headings and paragraphs.\n"
            ."Required body_ar sections (STRICT FORMAT):\n"
            ."- Use exactly six <h2> headings and they MUST start with numeric prefixes: 1. , 2. , 3. , 4. , 5. , 6.\n"
            ."- Section 1: intro about {$serviceNameAr} importance + mention Clean365.\n"
            ."- Section 2: why periodic maintenance/professional work is needed + bullet list of neglect risks.\n"
            ."- Section 3: Clean365 services for {$serviceNameAr} + ordered list with five numbered items (1..5) and short explanation under each.\n"
            ."- Section 4: Clean365 app advantages + bullet list.\n"
            ."- Section 5: how to choose the right {$serviceNameAr} provider + bullet list.\n"
            ."- Section 6: professional tip + concise closing CTA.\n"
            ."- Do not add any section before 1 or after 6.\n"
            ."- Do not append unrelated paragraphs, examples, or extra articles.\n"
            ."Do not output plain text blocks for body_ar/body_en; output proper HTML sections.\n"
            ."Use h2, h3, p, ul, ol, li, strong, a tags only.\n"
            ."For the services section, use an ordered list (ol) with visible numbering.\n"
            ."When listing major benefits or warnings, highlight lead phrase in <strong>.\n"
            ."Include internal links naturally in body content.\n"
            ."image_prompt must describe a marketing hero image in Arabic context with:\n"
            ."- One clear professional MALE technician only (no female), camera slightly zoomed out to show upper body and context.\n"
            ."- The male technician must be actively repairing an object directly tied to {$serviceNameAr} only.\n"
            ."- Keep the scene strictly related to {$serviceNameAr} only; do not include unrelated tools or appliances.\n"
            ."- Technician shirt must clearly contain the word CLEAN365 (English) as shirt print.\n"
            ."- IMPORTANT TEXT RULE: No text overlays in the scene, no headlines, no captions, no extra words at all. The only permitted text is CLEAN365 on the shirt.\n"
            ."- High-quality, sharp, premium, realistic-advertising look.\n"
            ."- Clean365 brand palette: primary teal {$brandHex} for cleaning cloth, tool accents and highlights; navy blue uniform polo and cap; bright modern home interior with white marble and warm natural lighting.\n"
            ."- NEVER use purple, violet, or mauve tones — only teal, navy, white, and neutral warm interiors.\n"
            ."- Clean composition suitable as blog featured image, no clutter.\n"
            ."Service data:\n"
            .json_encode([
                'service_name_ar' => $serviceNameAr,
                'service_name_en' => $serviceNameEn,
                'service_description_ar' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'ar'),
                'service_description_en' => \Modules\BlogModule\Support\BlogLocale::of($service, 'description', 'en'),
                'service_slug' => $service->slug,
                'category_name_ar' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'ar'),
                'category_name_en' => \Modules\BlogModule\Support\BlogLocale::of($category, 'name', 'en'),
                'category_slug' => $category?->slug,
                'internal_links' => $internalLinks,
                'client_extra_prompt' => $extraPrompt,
            ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  list<string>  $models
     * @param  array<string, mixed>  $generationConfig
     * @return array<string, mixed>
     */
    private function invokeGeminiText(
        string $prompt,
        array $models,
        array $generationConfig,
        string $failurePrefix,
        bool $forSeo = false,
    ): array {
        $options = $forSeo ? $this->vertexClient()->seoTextCallOptions() : null;

        return $this->vertexClient()->generateText($prompt, $models, $generationConfig, $failurePrefix, $options);
    }

    /**
     * @return list<string>
     */
    private function seoModelsToTry(): array
    {
        $preferred = $this->normalizeModelId((string) config(
            'services.gemini.seo_text_model',
            config('services.gemini.text_model', 'gemini-2.5-flash-lite')
        ));
        $fallbacks = array_map(
            fn (string $m) => $this->normalizeModelId($m),
            (array) config('services.gemini.seo_text_models', config('services.gemini.text_models', []))
        );
        $builtIn = ['gemini-3.6-flash', 'gemini-flash-latest', 'gemini-2.5-flash'];

        return array_values(array_unique(array_filter([$preferred, ...$fallbacks, ...$builtIn])));
    }

    /**
     * @return list<string>
     */
    private function textModelsToTry(): array
    {
        $preferred = $this->normalizeModelId((string) config('services.gemini.text_model', 'gemini-2.5-flash-lite'));
        $fallbacks = array_map(
            fn (string $m) => $this->normalizeModelId($m),
            (array) config('services.gemini.text_models', [])
        );
        $builtIn = ['gemini-3.6-flash', 'gemini-flash-latest', 'gemini-2.5-flash'];

        return array_values(array_unique(array_filter([$preferred, ...$fallbacks, ...$builtIn])));
    }

    /**
     * @return list<string>
     */
    private function baseUrlCandidates(): array
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
     * Tuned for structured SEO JSON (meta, keywords). No thinking budget.
     *
     * @return array<string, mixed>
     */
    private function seoGenerationConfig(): array
    {
        $config = [
            'maxOutputTokens' => max(4096, (int) config('services.gemini.max_output_tokens', 8192)),
            'temperature' => 0.35,
        ];

        if (! $this->vertexClient()->textUsesAiPlatform()) {
            $config['responseMimeType'] = 'application/json';
        }

        return $config;
    }

    private function normalizeModelId(string $model): string
    {
        $model = trim($model);

        if ($model === '') {
            return '';
        }

        $model = preg_replace('#^models/#', '', $model) ?? $model;

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    private function generationConfig(): array
    {
        $config = [
            'maxOutputTokens' => max(8192, (int) config('services.gemini.max_output_tokens', 32768)),
            'temperature' => 0.7,
        ];

        if (config('services.gemini.use_response_mime_type', false)) {
            $config['responseMimeType'] = 'application/json';
        }

        $thinking = config('services.gemini.thinking_budget');

        if ($thinking !== null && $thinking !== '') {
            $config['thinkingConfig'] = [
                'thinkingBudget' => (int) $thinking,
            ];
        }

        return $config;
    }

    /**
     * @param  ?\Throwable  $lastException  updated when a non-success response occurs
     * @return array<string, mixed>|null Decoded JSON body on success, or null to try next model/base.
     */
    private function requestGenerateContent(
        string $url,
        string $prompt,
        ?\Throwable &$lastException,
        ?array $generationConfig = null,
    ): ?array {
        $maxAttempts = 3;
        $generationConfig ??= $this->generationConfig();

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $timeout = max(60, (int) config('services.gemini.http_timeout', 180));
                $connectTimeout = max(30, (int) config('services.gemini.connect_timeout', 60));

                $httpResponse = Http::acceptJson()
                    ->connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => $generationConfig,
                    ]);

                if ($httpResponse->successful()) {
                    return $httpResponse->json();
                }

                $status = $httpResponse->status();
                $lastException = $httpResponse->toException();

                if (in_array($status, [400, 403, 404], true)) {
                    return null;
                }

                if (in_array($status, [503, 429, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                if (in_array($status, [503, 429, 408], true)) {
                    return null;
                }

                throw $lastException;
            } catch (RequestException $exception) {
                $lastException = $exception;
                $status = $exception->response?->status();

                if (in_array($status, [400, 403, 404], true)) {
                    return null;
                }

                if (in_array($status, [503, 429, 408], true) && $attempt < $maxAttempts) {
                    usleep(random_int(1_500_000, 4_000_000));

                    continue;
                }

                if (in_array($status, [503, 429, 408], true)) {
                    return null;
                }

                throw $exception;
            }
        }

        return null;
    }

    /**
     * Concatenate all text parts from the first candidate (some models split output across parts).
     */
    private function extractCandidateText(array $apiResponse): string
    {
        $parts = data_get($apiResponse, 'candidates.0.content.parts', []);

        if (! is_array($parts)) {
            return '';
        }

        $chunks = [];

        foreach ($parts as $part) {
            if (! is_array($part)) {
                continue;
            }

            if (isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return trim(implode("\n", $chunks));
    }

    /**
     * Parse model output into an array (handles markdown fences and leading/trailing prose).
     *
     * @return array<string, mixed>|null
     */
    private function decodeBlogJsonPayload(string $raw): ?array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```\s*$/', '', $raw) ?? $raw;
        $raw = trim($raw);

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $context
     * @return array{meta_title_ar:string,meta_title_en:string,meta_description_ar:string,meta_description_en:string}
     */
    private function generateSeoMetaPayload(array $context): array
    {
        $prompt = "You are an SEO metadata generator for Arabic and English content.\n"
            ."Return only valid JSON without markdown fences.\n"
            ."JSON keys exactly: meta_title_ar,meta_title_en,meta_description_ar,meta_description_en\n"
            ."Rules:\n"
            ."- meta_title_ar and meta_title_en must be concise and highly relevant.\n"
            ."- meta_description_ar and meta_description_en must be plain text (no HTML/markdown), clear, and compelling.\n"
            ."- Keep each title around 45-65 characters when possible.\n"
            ."- Keep each description around 120-160 characters when possible.\n"
            ."- Match the exact entity context and do not switch topic.\n"
            ."- Include brand tone for Clean365 naturally without stuffing.\n"
            ."Entity data:\n"
            .json_encode($context, JSON_UNESCAPED_UNICODE);

        $payload = $this->callJson($prompt, [
            'meta_title_ar',
            'meta_title_en',
            'meta_description_ar',
            'meta_description_en',
        ]);

        return [
            'meta_title_ar' => trim((string) ($payload['meta_title_ar'] ?? '')),
            'meta_title_en' => trim((string) ($payload['meta_title_en'] ?? '')),
            'meta_description_ar' => trim((string) ($payload['meta_description_ar'] ?? '')),
            'meta_description_en' => trim((string) ($payload['meta_description_en'] ?? '')),
        ];
    }
}
