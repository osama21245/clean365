<?php

namespace App\Models;

use App\Support\NotificationLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\UserManagement\Entities\User;

class AdBroadcast extends Model
{
    public const FCM_PENDING = 'pending';

    public const FCM_PROCESSING = 'processing';

    public const FCM_COMPLETED = 'completed';

    public const FCM_PARTIAL = 'partial';

    public const FCM_FAILED = 'failed';

    public const CONTENT_TARGET_NONE = 'none';

    public const CONTENT_TARGET_CATEGORY = 'category';

    public const CONTENT_TARGET_SUBCATEGORY = 'subcategory';

    public const CONTENT_TARGET_SERVICE = 'service';

    public const AUDIENCE_CUSTOMERS = 'customers';

    public const AUDIENCE_PROVIDERS = 'providers';

    public const AUDIENCE_SERVICEMEN = 'servicemen';

    public const AUDIENCE_GUESTS = 'guests';

    /**
     * FCM topic prefixes matching Clean365 subscribe pattern.
     */
    public const TOPIC_BY_AUDIENCE = [
        'customers' => 'customer',
        'providers' => 'provider-admin',
        'servicemen' => 'provider-serviceman',
        'guests' => 'guest',
    ];

    protected $fillable = [
        'audience',
        'content_target',
        'category_id',
        'subcategory_id',
        'service_id',
        'title',
        'description',
        'image_path',
        'user_id',
        'send_id',
        'zone_ids',
        'explicit_user_ids',
        'fcm_status',
        'recipient_estimate',
        'tokens_targeted',
        'tokens_success',
        'tokens_failure',
        'topic_dispatches_ok',
        'topic_dispatches_total',
        'laravel_batch_id',
        'opened_count',
        'fcm_summary',
    ];

    protected function casts(): array
    {
        return [
            'zone_ids' => 'array',
            'explicit_user_ids' => 'array',
            'fcm_summary' => 'array',
        ];
    }

    public function getTitleAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $map = NotificationLocale::decodeMap($value);

        return NotificationLocale::resolve($map, NotificationLocale::current());
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $map = NotificationLocale::decodeMap($value);

        return NotificationLocale::resolve($map, NotificationLocale::current());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(AdBroadcastDispatch::class);
    }

    public function acks(): HasMany
    {
        return $this->hasMany(AdBroadcastAck::class);
    }

    /**
     * @return list<string>
     */
    public function audienceKeys(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->audience))));
    }

    public function publicImageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('ad-media')->url((string) $this->image_path);
    }

    public function effectiveContentTarget(): string
    {
        $target = $this->content_target;

        if ($target === null || $target === '') {
            return self::CONTENT_TARGET_NONE;
        }

        return (string) $target;
    }

    public static function topicPrefix(string $audienceKey): ?string
    {
        return self::TOPIC_BY_AUDIENCE[$audienceKey] ?? null;
    }

    public static function topicFor(string $audienceKey, ?string $zoneId = null): ?string
    {
        $prefix = self::topicPrefix($audienceKey);
        if ($prefix === null) {
            return null;
        }

        if ($audienceKey === self::AUDIENCE_GUESTS || $zoneId === null || $zoneId === '') {
            return $prefix;
        }

        return $prefix.'-'.$zoneId;
    }
}
