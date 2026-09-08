<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Admin Blade / session: POST /broadcasting/auth
        Broadcast::routes(['middleware' => ['web', 'auth:web,api']]);

        // Mobile apps (Passport Bearer): POST /api/broadcasting/auth
        Broadcast::routes([
            'middleware' => ['auth:api'],
            'prefix' => 'api',
        ]);

        require base_path('routes/channels.php');
    }
}
