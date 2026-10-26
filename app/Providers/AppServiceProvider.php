<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
{
    Event::listen(Login::class, function (Login $event) {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
    });
}
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

   
}
