<?php

namespace Modules\BlogModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\BlogModule\Support\FeaturedImageHelper;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class Article extends Model
{
    use HasUuid;

    protected $table = 'blog_articles';

    protected $fillable = [
        'service_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'generated_by_ai',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'body' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'meta_keywords' => 'array',
        'generated_by_ai' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = ['featured_image_full_path', 'title_default'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function storage_featured_image()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'featured_image');
    }

    public function localeField(string $field, string $locale): string
    {
        $value = $this->{$field};

        if (is_array($value)) {
            return (string) ($value[$locale] ?? $value['en'] ?? $value['ar'] ?? '');
        }

        return is_string($value) ? $value : '';
    }

    public function getTitleDefaultAttribute(): string
    {
        return $this->localeField('title', app()->getLocale() === 'ar' ? 'ar' : 'en');
    }

    public function getFeaturedImageFullPathAttribute(): ?string
    {
        $image = $this->featured_image;
        if (!$image) {
            return null;
        }

        $disk = $this->storage_featured_image?->storage_type ?? 'public';

        return FeaturedImageHelper::resolvePublicUrl($image, $disk);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public static function generateUniqueSlug(string $source): string
    {
        $slug = Str::slug($source);
        if ($slug === '') {
            $slug = 'article-' . Str::random(8);
        }

        $base = $slug;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
