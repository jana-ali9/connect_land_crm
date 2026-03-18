<?php

namespace App\Providers;

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
       
        if (str_contains(config('app.url'), 'ngrok-free.dev') || config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        ini_set('upload_max_filesize', env('UPLOAD_MAX_SIZE', '100M'));
        ini_set('post_max_size', env('UPLOAD_MAX_SIZE', '110M'));
        
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
    }
}
