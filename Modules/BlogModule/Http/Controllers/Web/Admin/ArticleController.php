<?php

namespace Modules\BlogModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BlogModule\Entities\Article;
use Modules\BlogModule\Jobs\GenerateSeoArticleForServiceJob;
use Modules\BlogModule\Support\BlogAutomationHealth;
use Modules\BlogModule\Support\BlogAutomationSettings;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, BlogAutomationHealth $automationHealth): View
    {
        $this->authorize('blog_view');

        $search = $request->get('search', '');
        $tab = $request->get('tab', 'list');

        $articles = Article::with(['service', 'category'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('slug', 'like', "%{$search}%")
                        ->orWhere('title->ar', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends(['search' => $search, 'tab' => $tab]);

        $settings = BlogAutomationSettings::all();
        $todayCount = Article::where('generated_by_ai', true)->whereDate('created_at', now()->toDateString())->count();
        $monthCount = Article::where('generated_by_ai', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $publishedCount = Article::published()->count();
        $totalCount = Article::count();

        $automationStatus = $automationHealth->snapshot($todayCount, $monthCount);

        return view('blogmodule::admin.articles.index', compact(
            'articles',
            'search',
            'tab',
            'settings',
            'todayCount',
            'monthCount',
            'publishedCount',
            'totalCount',
            'automationStatus'
        ));
    }

    public function create(): View
    {
        $this->authorize('blog_add');
        $services = Service::active()->latest()->get();
        $categories = Category::ofStatus(1)->ofType('main')->latest()->get();

        return view('blogmodule::admin.articles.create', compact('services', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('blog_add');

        $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_articles,slug',
            'body_ar' => 'required|string',
            'body_en' => 'required|string',
            'service_id' => 'nullable|uuid|exists:services,id',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'featured_image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image'),
        ]);

        $slug = $request->slug
            ? Article::generateUniqueSlug($request->slug)
            : Article::generateUniqueSlug($request->title_en ?: $request->title_ar);

        $article = Article::create([
            'title' => ['ar' => $request->title_ar, 'en' => $request->title_en],
            'slug' => $slug,
            'excerpt' => [
                'ar' => $request->excerpt_ar ?? '',
                'en' => $request->excerpt_en ?? '',
            ],
            'body' => ['ar' => $request->body_ar, 'en' => $request->body_en],
            'meta_title' => [
                'ar' => $request->meta_title_ar ?? $request->title_ar,
                'en' => $request->meta_title_en ?? $request->title_en,
            ],
            'meta_description' => [
                'ar' => $request->meta_description_ar ?? '',
                'en' => $request->meta_description_en ?? '',
            ],
            'service_id' => $request->service_id,
            'category_id' => $request->category_id,
            'generated_by_ai' => false,
            'is_active' => (bool) $request->boolean('is_active', true),
            'published_at' => $request->boolean('is_active', true) ? now() : null,
        ]);

        if ($request->hasFile('featured_image')) {
            $article->featured_image = file_uploader('blog/', 'png', $request->file('featured_image'));
            $article->save();
            saveSingleImageDataToStorage($article, 'featured_image', getDisk());
        }

        Toastr::success(translate('Article created successfully'));

        return redirect()->route('admin.blog.list');
    }

    public function edit(string $id): View
    {
        $this->authorize('blog_update');
        $article = Article::findOrFail($id);
        $services = Service::active()->latest()->get();
        $categories = Category::ofStatus(1)->ofType('main')->latest()->get();

        return view('blogmodule::admin.articles.edit', compact('article', 'services', 'categories'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('blog_update');
        $article = Article::findOrFail($id);

        $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_articles,slug,' . $article->id,
            'body_ar' => 'required|string',
            'body_en' => 'required|string',
            'service_id' => 'nullable|uuid|exists:services,id',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'featured_image' => 'nullable|image|max:' . uploadMaxFileSizeInKB('image'),
        ]);

        $article->update([
            'title' => ['ar' => $request->title_ar, 'en' => $request->title_en],
            'slug' => $request->slug ?: $article->slug,
            'excerpt' => [
                'ar' => $request->excerpt_ar ?? '',
                'en' => $request->excerpt_en ?? '',
            ],
            'body' => ['ar' => $request->body_ar, 'en' => $request->body_en],
            'meta_title' => [
                'ar' => $request->meta_title_ar ?? $request->title_ar,
                'en' => $request->meta_title_en ?? $request->title_en,
            ],
            'meta_description' => [
                'ar' => $request->meta_description_ar ?? '',
                'en' => $request->meta_description_en ?? '',
            ],
            'service_id' => $request->service_id,
            'category_id' => $request->category_id,
            'is_active' => $request->boolean('is_active'),
            'published_at' => $request->boolean('is_active')
                ? ($article->published_at ?? now())
                : null,
        ]);

        if ($request->hasFile('featured_image')) {
            $article->featured_image = file_uploader('blog/', 'png', $request->file('featured_image'), $article->featured_image);
            $article->save();
            saveSingleImageDataToStorage($article, 'featured_image', getDisk());
        }

        Toastr::success(translate('Article updated successfully'));

        return back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->authorize('blog_delete');
        Article::where('id', $id)->delete();
        Toastr::success(translate('Article deleted successfully'));

        return back();
    }

    public function statusUpdate(string $id): RedirectResponse
    {
        $this->authorize('blog_manage_status');
        $article = Article::findOrFail($id);
        $article->is_active = !$article->is_active;
        if ($article->is_active && !$article->published_at) {
            $article->published_at = now();
        }
        $article->save();

        Toastr::success(translate('Status updated successfully'));

        return back();
    }

    public function automation(): RedirectResponse
    {
        return redirect()->route('admin.blog.list', ['tab' => 'automation']);
    }

    public function updateAutomation(Request $request): RedirectResponse
    {
        $this->authorize('blog_update');

        $request->validate([
            'ai_blog_daily_limit' => 'required|integer|min:1|max:100',
            'ai_blog_monthly_limit' => 'required|integer|min:1|max:1000',
            'ai_blog_prompt' => 'nullable|string|max:5000',
        ]);

        BlogAutomationSettings::update([
            'ai_blog_automation_enabled' => $request->boolean('ai_blog_automation_enabled'),
            'ai_blog_daily_limit' => $request->integer('ai_blog_daily_limit'),
            'ai_blog_monthly_limit' => $request->integer('ai_blog_monthly_limit'),
            'ai_blog_prompt' => $request->input('ai_blog_prompt'),
        ]);

        Toastr::success(translate('Blog automation settings updated'));

        return redirect()->route('admin.blog.list', ['tab' => 'automation']);
    }

    public function generateNow(Request $request): RedirectResponse
    {
        $this->authorize('blog_add');

        $limit = max(1, min(10, (int) $request->input('limit', 1)));
        $services = Service::latest('id')->take($limit)->get();

        foreach ($services as $service) {
            GenerateSeoArticleForServiceJob::dispatch($service->id, true, true);
        }

        Toastr::success(translate('Article generation queued. Refresh in a minute to review it.'));

        return back();
    }
}
