<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\UserManagement\Entities\User;

class CustomerServiceSubscription extends Model
{
    use HasUuid;

    protected $casts = [
        'total_visits' => 'integer',
        'remaining_visits' => 'integer'
    ];

    protected $appends = [
        'used_visits',
        'price'
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'property_id',
        'total_visits',
        'remaining_visits',
        'status'
    ];

    /** @var float|null Runtime-only package price for API responses (not a DB column). */
    public ?float $apiPrice = null;

    public function getUsedVisitsAttribute(): int
    {
        return max(0, (int) $this->total_visits - (int) $this->remaining_visits);
    }

    /**
     * Package price for the current zone (set via apiPrice / presentForApi).
     */
    public function getPriceAttribute(): float
    {
        return round((float) ($this->apiPrice ?? 0), 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Historical subscriptions must still resolve soft-deleted / scoped services.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id')
            ->withoutGlobalScopes()
            ->withTrashed();
    }

    public function additional_service(): BelongsTo
    {
        return $this->belongsTo(AdditionalService::class, 'service_id')
            ->withoutGlobalScopes();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(CustomerServiceSubscriptionVisit::class, 'subscription_id');
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
