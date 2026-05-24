<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
class AppServiceProvider extends ServiceProvider
{
/**
* Register any application services.
*/
public function register(): void
{
//
}
/**
* Bootstrap any application services.
*/
public function boot(): void
{
if (env('APP_ENV') === 'production') {
URL::forceScheme('https');
}
		// If storage/logs isn't writable (common on some hosts/containers),
		// fall back to the `errorlog` channel to avoid StreamHandler exceptions.
		$logDir = storage_path('logs');
		if (!is_dir($logDir) || !is_writable($logDir)) {
			Config::set('logging.default', env('LOG_CHANNEL', 'errorlog'));
		}
}
}