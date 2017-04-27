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
	    $this->publishes([
	        __DIR__.'/config/config.php' => config_path('sms.php')
	    ], 'smsconfig');
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Sms', function () {
            return new SmsManager($this->app);
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Sms'];
    }

}
