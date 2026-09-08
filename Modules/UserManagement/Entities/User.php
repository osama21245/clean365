<?php

namespace Modules\UserManagement\Entities;

use App\Models\UserInboxNotification;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\SettingsTutorials;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\CartModule\Entities\AddedToCart;
use Modules\ChattingModule\Entities\ChannelConversation;
use Modules\CustomerModule\Entities\SearchedData;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ReviewModule\Entities\Review;
use Modules\ServiceManagement\Entities\VisitedService;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\ZoneManagement\Entities\Zone;
use Laravel\Passport\Token;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    use HasFactory, HasUuid;

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'is_phone_verified' => 'integer',
        'is_email_verified' => 'integer',
        'is_active' => 'integer',
        'identification_image' => 'array',
        'wallet_balance' => 'float',
        'loyalty_point' => 'float',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = ['profile_image_full_path', 'identification_image_full_path', 'full_name', 'name'];

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'identification_number',
        'identification_type',
        'identification_image',
        'date_of_birth',
        'gender',
        'profile_image',
        'fcm_token',
        'latitude',
        'longitude',
        'last_seen_at',
        'is_phone_verified',
        'is_email_verified',
        'phone_verified_at',
        'email_verified_at',
        'password',
        'is_active',
        'provider_id',
        'user_type',
        'wallet_balance',
        'loyalty_point',
        'ref_code',
        'referred_by'
    ];

    public static function onlineThresholdMinutes(): int
    {
        return max(1, (int) config('tracking.online_threshold_minutes', 30));
    }

    public static function onlineSince(): Carbon
    {
        return now()->subMinutes(static::onlineThresholdMinutes());
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gte(static::onlineSince());
    }

    public function scopeIsOnline(Builder $query): Builder
    {
        return $query->where('last_seen_at', '>=', static::onlineSince());
    }

    public function scopeHasLocation(Builder $query): Builder
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeIsFieldAgent(Builder $query): Builder
    {
        return $query->whereIn('user_type', ['provider-admin', 'provider-serviceman']);
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'employee_role_sections', 'employee_id');
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id', 'id');
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function zones(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'user_zones');
    }

    public function inboxNotifications(): HasMany
    {
        return $this->hasMany(UserInboxNotification::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    protected function scopeOfType($query, array $type)
    {
        $query->whereIn('user_type', $type);
    }

    protected function scopeOfStatus($query, $status)
    {
        $query->where('is_active', $status);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function referred_by_user()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function provider(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Provider::class);
    }

    public function serviceman(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Serviceman::class);
    }

    public function transactions_for_from_user(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_user_id');
    }

    public function added_to_carts(): HasMany
    {
        return $this->hasMany(AddedToCart::class, 'user_id', 'id');
    }

    public function visited_services(): HasMany
    {
        return $this->hasMany(VisitedService::class, 'user_id', 'id');
    }

    public function searched_data(): HasMany
    {
        return $this->hasMany(SearchedData::class, 'user_id', 'id');
    }

    public function channelConversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChannelConversation::class, 'user_id', 'id');
    }

    public function module_access(): HasMany
    {
        return $this->hasMany(EmployeeRoleAccess::class, 'employee_id', 'id');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getProfileImageFullPathAttribute()
    {
        $image = $this->profile_image;
        $defaultPath = $this->user_type == 'customer' ? asset('public/assets/admin-module/img/customer.png') : asset('public/assets/provider-module/img/user2x.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = '';

        if ($this->user_type == 'admin-employee') {
            $path = 'employee/profile/';
        } else if ($this->user_type == 'customer' || $this->user_type == 'super-admin') {
            $path = 'user/profile_image/';
        } else if ($this->user_type == 'provider-serviceman') {
            $path = 'serviceman/profile/';
        } else if ($this->user_type == 'provider-admin') {
            $path = 'provider/logo/';
        }

        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    public function getIdentificationImageFullPathAttribute()
    {
        $identityImages = $this->identification_image ?? [];
        $defaultImagePath = asset('public/assets/admin-module/img/media/provider-id.png');

        if (empty($identityImages)) {
            if (request()->is('api/*')) {
                $defaultImagePath = null;

                return $defaultImagePath ? [$defaultImagePath] : [];
            }
            return [];
        }

        $path = '';
        if ($this->user_type == 'admin-employee') {
            $path = 'employee/identity/';
        } else if ($this->user_type == 'provider-admin') {
            $path = 'provider/identity/';
        } else if ($this->user_type == 'provider-serviceman') {
            $path = 'serviceman/identity/';
        }

        return getIdentityImageFullPath(identityImages: $identityImages, path: $path, defaultPath: $defaultImagePath);
    }

    public function tutorials()
    {
        return $this->hasMany(SettingsTutorials::class);
    }

    public function getTutorialByPlatform($platform)
    {
        return $this->tutorials()->where('platform', $platform)->first();
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->ref_code = generate_referer_code();
        });

        self::created(function ($model) {
            $account = new Account();
            $account->user_id = $model->id;
            $account->save();
        });

        self::updating(function ($model) {
            if ($model->isDirty('is_active')) {
                if ($model->is_active == 0) {
                    $model->fcm_token = '';
                }
            }
        });

        self::updated(function ($model) {
            if ($model->isDirty('is_active')) {

                if ($model->is_active == 0) {

                    $title = translate('Your account has been deactivated! Please contact with admin');
                    if ($model->fcm_token && $title) {
                        device_notification($model->fcm_token, $title, null, null, null, 'logout', null, $model->id);
                    }

                    $model->tokens->each(function ($token, $key) {
                        $token->revoke();
                    });
                }
            }
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });

        static::saved(function ($model) {
            $storageType = getDisk();
            if ($model->isDirty('profile_image')) {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'profile_image', storageType: $storageType);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        $firstName = trim($this->first_name ?? '');
        $lastName = trim($this->last_name ?? '');

        if ($firstName !== '' && $lastName !== '') {
            return $firstName . ' ' . $lastName;
        }

        return $firstName !== '' ? $firstName : $lastName;
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }
}
