<?php

namespace Modules\ServicemanModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SupervisorTeamServiceman extends Pivot
{
    use HasUuid;

    protected $table = 'supervisor_team_servicemen';

    public $incrementing = false;

    protected $keyType = 'string';
}
