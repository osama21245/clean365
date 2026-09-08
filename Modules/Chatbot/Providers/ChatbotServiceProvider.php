<?php

namespace Modules\Chatbot\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Chatbot\Console\SyncPackagesToPinecone;
use Modules\Chatbot\Observers\ServicePineconeObserver;
use Modules\ServiceManagement\Entities\Service;

class ChatbotServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Chatbot';

    protected string $moduleNameLower = 'chatbot';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if (class_exists(Service::class)) {
            Service::observe(ServicePineconeObserver::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPackagesToPinecone::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/ai-automation.php'),
            'ai-automation'
        );
    }
}
