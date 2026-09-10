<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class OfferBanner extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'offer_banners';

    protected $fillable = [
        'title',
        'subtitle',
        'original_price',
        'offer_price',
        'tag',
        'discount_badge',
        'coupon_code',
        'resource_type',
        'resource_id',
        'redirect_link',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'integer',
        'original_price' => 'float',
        'offer_price' => 'float',
    ];

    protected $appends = ['image_full_path'];

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'resource_id');
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class, 'resource_id');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getImageFullPathAttribute()
    {
        $image = $this->image;
        $defaultPath = asset('public/assets/admin-module/img/media/banner-upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'offer_banner/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }
}
