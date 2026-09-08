<?php

namespace Modules\ServicemanModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookingModule\Entities\Booking;
use Modules\UserManagement\Entities\Serviceman;

class BookingAttendance extends Model
{
    use HasUuid;

    protected $fillable = [
        'booking_id',
        'serviceman_id',
        'is_attended',
        'attended_at'];

    protected $casts = [
        'is_attended' => 'boolean',
        'attended_at' => 'datetime'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class, 'serviceman_id');
    }
}
