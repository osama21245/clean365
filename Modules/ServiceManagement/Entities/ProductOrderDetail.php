<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOrderDetail extends Model
{
    use HasUuid;

    protected $casts = [
        'product_price' => 'float',
        'sale_price' => 'float',
        'unit_price' => 'float',
        'quantity' => 'integer',
        'total_price' => 'float'];

    protected $fillable = [
        'product_order_id',
        'product_id',
        'product_name',
        'thumbnail',
        'product_price',
        'sale_price',
        'unit_price',
        'quantity',
        'total_price'];

    protected $appends = ['thumbnail_full_path'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getThumbnailFullPathAttribute()
    {
        $image = $this->thumbnail;
        $defaultPath = asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                return null;
            }
            return $defaultPath;
        }

        $imagePath = 'product/' . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: null, defaultPath: $defaultPath);
    }
}
