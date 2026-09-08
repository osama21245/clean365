<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;

class ProductCategory extends Model
{
    use HasUuid;

    protected $casts = [
        'is_active' => 'integer'];

    protected $fillable = [
        'name',
        'image',
        'is_active'];

    protected $appends = ['image_full_path'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function storage_image(): HasOne
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'image');
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

    public function getImageFullPathAttribute()
    {
        $image = $this->image;
        $defaultPath = asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                return null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_image;
        $imagePath = 'product-category/' . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'image', storageType: getDisk());
            }
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
