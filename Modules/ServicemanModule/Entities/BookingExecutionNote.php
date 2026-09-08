<?php

namespace Modules\ServicemanModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookingModule\Entities\Booking;
use Modules\UserManagement\Entities\User;

class BookingExecutionNote extends Model
{
    use HasUuid;

    protected $fillable = [
        'booking_id',
        'user_id',
        'note'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
