<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\BusinessSettingsModule\Entities\Storage;

class AdditionalService extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'additional_services';

    protected $fillable = [
        'name',
        'price',
        'icon',
        'features',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['icon_full_path', 'thumbnail_full_path', 'cover_image_full_path', 'thumbnail', 'cover_image'];

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function service_discount(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\PromotionManagement\Entities\DiscountType::class, 'type_wise_id')
            ->whereHas('discount', function ($query) {
                $query->whereIn('discount_type', ['additional_service', 'service'])
                    ->where('promotion_type', 'discount')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->with(['discount'])->latest();
    }

    public function getIconFullPathAttribute(): ?string
    {
        $icon = $this->icon;
        if (!$icon) {
            return null;
        }

        if (filter_var($icon, FILTER_VALIDATE_URL)) {
            return $icon;
        }

        $defaultPath = asset('public/assets/placeholder.png');
        $path = 'additional_service/';
        $imagePath = $path . $icon;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $this->storage, defaultPath: $defaultPath);
    }

    public function getThumbnailAttribute(): ?string
    {
        return $this->icon;
    }

    public function getCoverImageAttribute(): ?string
    {
        return $this->icon;
    }

    public function getThumbnailFullPathAttribute(): ?string
    {
        return $this->icon_full_path;
    }

    public function getCoverImageFullPathAttribute(): ?string
    {
        return $this->icon_full_path;
    }

    public function setFeaturesAttribute($value): void
    {
        $this->attributes['features'] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    public function getFeaturesAttribute($value): array
    {
        $items = is_string($value) ? (json_decode($value, true) ?? []) : (is_array($value) ? $value : []);
        if (!is_array($items)) {
            return [];
        }

        return collect($items)->map(function ($item) {
            if (is_string($item)) {
                return [
                    'title' => $item,
                    'price' => 0,
                    'icon' => null,
                    'icon_full_path' => null
                ];
            }

            $icon = $item['icon'] ?? null;
            $iconFullPath = null;
            if ($icon) {
                if (filter_var($icon, FILTER_VALIDATE_URL)) {
                    $iconFullPath = $icon;
                } else {
                    $defaultPath = asset('public/assets/placeholder.png');
                    $iconFullPath = getSingleImageFullPath(imagePath: 'additional_service/' . $icon, s3Storage: $this->storage, defaultPath: $defaultPath);
                }
            }

            return [
                'title' => $item['title'] ?? '',
                'price' => isset($item['price']) ? (float) $item['price'] : 0,
                'icon' => $icon,
                'icon_full_path' => $iconFullPath,
            ];
        })->values()->all();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
