<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;

class Product extends Model
{
    use HasUuid;

    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'rating' => 'float',
        'is_active' => 'integer'];

    protected $fillable = [
        'name',
        'short_description',
        'price',
        'sale_price',
        'badge',
        'thumbnail',
        'product_category_id',
        'rating',
        'is_active'];

    protected $appends = ['thumbnail_full_path', 'final_price', 'discount_percent'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function storage_thumbnail(): HasOne
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'thumbnail');
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('is_active', $status);
    }

    public function getNameAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getShortDescriptionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'short_description') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getFinalPriceAttribute(): float
    {
        if (!is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    public function getDiscountPercentAttribute(): float
    {
        if (!is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100, 2);
        }

        return 0;
    }

    public function getThumbnailFullPathAttribute()
    {
        $image = $this->thumbnail;
        $defaultPath = request()->is('*/edit/*')
            ? asset('public/assets/admin-module/img/media/upload-file.png')
            : asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                return null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_thumbnail;
        $imagePath = 'product/' . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->isDirty('thumbnail')) {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'thumbnail', storageType: getDisk());
            }
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
