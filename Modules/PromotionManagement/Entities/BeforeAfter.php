<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Entities\Storage;

class BeforeAfter extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'before_afters';

    protected $fillable = [];

    protected $casts = [
        'is_active' => 'integer',
        'sort_order' => 'integer'];

    protected $appends = [
        'before_image_full_path',
        'after_image_full_path'];

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function storage_before_image()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'before_image');
    }

    public function storage_after_image()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'after_image');
    }

    public function getBeforeImageFullPathAttribute()
    {
        $image = $this->before_image;
        $defaultPath = asset('public/assets/admin-module/img/media/banner-upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                return null;
            }
            return $defaultPath;
        }

        return getSingleImageFullPath(
            imagePath: 'before-after/' . $image,
            s3Storage: $this->storage_before_image,
            defaultPath: $defaultPath
        );
    }

    public function getAfterImageFullPathAttribute()
    {
        $image = $this->after_image;
        $defaultPath = asset('public/assets/admin-module/img/media/banner-upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                return null;
            }
            return $defaultPath;
        }

        return getSingleImageFullPath(
            imagePath: 'before-after/' . $image,
            s3Storage: $this->storage_after_image,
            defaultPath: $defaultPath
        );
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            $storageType = getDisk();
            if ($model->isDirty('before_image')) {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'before_image', storageType: $storageType);
            }
            if ($model->isDirty('after_image')) {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'after_image', storageType: $storageType);
            }
        });
    }
}
