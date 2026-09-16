<?php

namespace App\Providers;

use App\Listeners\LogFailedLogin;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\AppSetting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);
        Event::listen(Failed::class, LogFailedLogin::class);

        // Force HTTPS URL scheme saat berjalan di balik Cloudflare Tunnel / Reverse Proxy
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://') || isset($_SERVER['HTTP_CF_VISITOR'])) {
            URL::forceScheme('https');
        }

        // Dinamisasi Nama Aplikasi & Branding
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            config(['app.name' => AppSetting::getSiteName()]);
        }
    }
}
