<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\UserManagement\Entities\User;

class ProductOrder extends Model
{
    use HasUuid;

    protected $casts = [
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'delivery_fee' => 'float',
        'total' => 'float',
        'total_quantity' => 'integer'];

    protected $fillable = [
        'customer_id',
        'order_status',
        'delivery_lat',
        'delivery_lng',
        'delivery_address',
        'subtotal',
        'discount_amount',
        'delivery_fee',
        'total',
        'coupon_code',
        'payment_method',
        'note',
        'total_quantity'];

    public function details(): HasMany
    {
        return $this->hasMany(ProductOrderDetail::class, 'product_order_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function scopeOfStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('order_status', $status);
        }

        return $query;
    }
}
