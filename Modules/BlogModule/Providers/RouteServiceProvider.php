<?php

namespace Modules\BlogModule\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\BlogModule\Http\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapApiV1Routes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BlogModule', '/Routes/web.php'));
    }

    protected function mapApiV1Routes(): void
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BlogModule', '/Routes/api/v1/api.php'));
    }
}
