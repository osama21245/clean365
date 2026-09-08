<?php

namespace Modules\ServicemanModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;

class SupervisorTeam extends Model
{
    use HasUuid;

    protected $fillable = [
        'provider_id',
        'name',
        'is_active'];

    protected $casts = [
        'is_active' => 'boolean'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function servicemen(): BelongsToMany
    {
        return $this->belongsToMany(
            Serviceman::class,
            'supervisor_team_servicemen',
            'team_id',
            'serviceman_id'
        )->using(SupervisorTeamServiceman::class)->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'team_id');
    }
}
