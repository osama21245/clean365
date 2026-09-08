<?php

namespace Modules\ServicemanModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;

class BookingSupportRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'booking_id',
        'provider_id',
        'type',
        'message',
        'attachments',
        'status'];

    protected $casts = [
        'attachments' => 'array'];

    protected $appends = ['attachments_full_path'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function getAttachmentsFullPathAttribute(): array
    {
        $images = $this->attachments ?? [];
        if (empty($images)) {
            return [];
        }

        return getIdentityImageFullPath(
            identityImages: $images,
            path: 'booking/support/',
            defaultPath: null
        );
    }
}
