<?php

namespace Modules\BlogModule\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\BlogModule\Console\GenerateSeoArticles;
use Modules\BlogModule\Console\RegenerateBlogFeaturedImages;
use Modules\BlogModule\Console\RegenerateCatalogImages;
use Modules\BlogModule\Console\TestBlogImageGeneration;

class BlogModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'BlogModule';

    protected string $moduleNameLower = 'blogmodule';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                RegenerateBlogFeaturedImages::class,
                RegenerateCatalogImages::class,
                TestBlogImageGeneration::class,
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
    }

    public function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }
}
