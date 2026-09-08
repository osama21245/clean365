<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;

class Package extends Model
{
    use HasUuid;

    protected $casts = [
        'price' => 'float',
        'is_active' => 'integer',
        'features' => 'array'];

    protected $fillable = [
        'name',
        'price',
        'billing_period',
        'features',
        'thumbnail',
        'is_active',
        'property_id'];

    protected $appends = ['thumbnail_full_path'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
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

    public function getThumbnailFullPathAttribute()
    {
        $image = $this->thumbnail;
        $defaultPath = request()->is('*/edit/*')
            ? asset('public/assets/admin-module/img/media/upload-file.png')
            : asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_thumbnail;
        $path = 'package/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }
}
