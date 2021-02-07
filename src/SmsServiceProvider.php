<?php

namespace Hinet\Sms;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (!function_exists('config_path')) {
            /**
             * Get the configuration path.
             *
             * @param string $path
             * @return string
             */
            function config_path($path = '')
            {
                return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
            }
        }
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('sms.php')
        ], 'smsconfig');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    	$this->mergeConfigFrom(__DIR__ . '/config/config.php', 'sms');
        $this->app->singleton('sms', function ($app) {
            return new SmsManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sms'];
    }

}
