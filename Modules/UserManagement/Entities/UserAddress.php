<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ServiceManagement\Entities\Property;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\UserManagement\Database\factories\UserAddressFactory::new();
    }
}
