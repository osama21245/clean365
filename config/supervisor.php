<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supervisor mode (internal employees)
    |--------------------------------------------------------------------------
    |
    | When enabled, providers are treated as internal supervisors/employees.
    | Subscription package limits, feature gates, and billing checks are bypassed.
    |
    */
    'enabled' => env('SUPERVISOR_MODE', true)];
