<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

ini_set('memory_limit', '512M');

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        app()->singleton('dynamic.s3', function () {
            $data = business_config('s3_storage_credentials', 'storage_settings')?->live_values;

            if (!$data) {
                return null;
            }

            $config = json_decode($data);

            return Storage::build([
                'driver' => 's3',
                'key' => $config->key,
                'secret' => $config->secret,
                'region' => $config->region,
                'bucket' => $config->bucket,
                'endpoint' => $config->endpoint,
                'use_path_style_endpoint' => $config->use_path_style_endpoint,
                'url' => $config->url,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot(Request $request)
    {
        if(env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }

        Config::set('external_admin_routes', []);
        Config::set('payment_gateway_publish_status', []);

        try {
            Config::set('default_pagination', 25);
            Paginator::useBootstrap();
        } catch (\Exception $ex) {
            info($ex);
        }
    }

    protected function registerAliases(array $aliases): void
    {
        $loader = AliasLoader::getInstance();

        foreach ($aliases as $alias => $class) {
            if (class_exists($class)) {
                $loader->alias($alias, $class);
            }
        }
    }
}
