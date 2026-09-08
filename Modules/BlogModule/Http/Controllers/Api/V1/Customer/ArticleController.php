<?php

namespace Modules\BlogModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BlogModule\Entities\Article;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) ($request->get('limit', pagination_limit()));
        $offset = (int) ($request->get('offset', 1));

        $paginator = Article::published()
            ->with(['service:id,name,slug', 'category:id,name,slug'])
            ->latest('published_at')
            ->paginate($limit, ['*'], 'page', $offset);

        $locale = $request->header('X-localization', app()->getLocale()) === 'ar' ? 'ar' : 'en';

        $items = collect($paginator->items())->map(function (Article $article) use ($locale) {
            return $this->transform($article, $locale);
        });

        return response()->json([
            'response_code' => 'default_200',
            'message' => translate('Data retrieved successfully'),
            'content' => [
                'data' => $items,
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::published()
            ->with(['service:id,name,slug', 'category:id,name,slug'])
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->first();

        if (!$article) {
            return response()->json([
                'response_code' => 'default_404',
                'message' => translate('Data not found'),
            ], 404);
        }

        $locale = request()->header('X-localization', app()->getLocale()) === 'ar' ? 'ar' : 'en';

        return response()->json([
            'response_code' => 'default_200',
            'message' => translate('Data retrieved successfully'),
            'content' => $this->transform($article, $locale, true),
        ]);
    }

    private function transform(Article $article, string $locale, bool $full = false): array
    {
        $data = [
            'id' => $article->id,
            'slug' => $article->slug,
            'title' => $article->localeField('title', $locale),
            'excerpt' => $article->localeField('excerpt', $locale),
            'featured_image' => $article->featured_image_full_path,
            'published_at' => optional($article->published_at)?->toIso8601String(),
            'generated_by_ai' => (bool) $article->generated_by_ai,
            'service_id' => $article->service_id,
            'category_id' => $article->category_id,
            'meta_title' => $article->localeField('meta_title', $locale),
            'meta_description' => $article->localeField('meta_description', $locale),
        ];

        if ($full) {
            $data['body'] = $article->localeField('body', $locale);
            $data['title_ar'] = $article->localeField('title', 'ar');
            $data['title_en'] = $article->localeField('title', 'en');
            $data['body_ar'] = $article->localeField('body', 'ar');
            $data['body_en'] = $article->localeField('body', 'en');
            $data['meta_keywords'] = $article->meta_keywords[$locale] ?? [];
        }

        return $data;
    }
}
