<?php

namespace CdnServices;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use CdnServices\Adapters\CdnServicesFilesystemAdapter;

class CdnServicesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cdn-services.php',
            'cdn-services'
        );

        $this->app->singleton('cdn-services.api', function ($app) {
            $config = config('cdn-services', []);
            if (empty($config['base_url'])) {
                $config = array_merge($config, config('filesystems.disks.cdn-services', []));
            }
            return new CdnServicesApi($config);
        });

        $this->app->singleton('cdn-services.auth', function ($app) {
            $config = config('cdn-services', []);
            if (empty($config['base_url'])) {
                $config = array_merge($config, config('filesystems.disks.cdn-services', []));
            }
            return new CdnServicesAuthService($config);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/cdn-services.php' => config_path('cdn-services.php'),
        ], 'cdn-services-config');

        // Extend Laravel's Storage with CDN Services driver
        Storage::extend('cdn-services', function ($app, $config) {
            return new CdnServicesFilesystemAdapter($config);
        });
    }
}

