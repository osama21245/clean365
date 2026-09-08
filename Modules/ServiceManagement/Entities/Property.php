<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\UserManagement\Entities\UserAddress;

class Property extends Model
{
    use HasUuid;

    protected $casts = [
        'is_active' => 'integer'];

    protected $fillable = [
        'name',
        'description',
        'address',
        'is_active'];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'property_id', 'id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'property_id', 'id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CustomerServiceSubscription::class, 'property_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'property_id', 'id');
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('is_active', $status);
    }
}
