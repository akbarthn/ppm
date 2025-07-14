<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        // Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale('id');

        // Set locale PHP untuk tanggal (untuk mendukung Linux/Windows)
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'Indonesian', 'Indonesia');
    }
}
