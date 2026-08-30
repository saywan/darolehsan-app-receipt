<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\NegarSmsService; // کلاس سرویس ایمپورت شود




class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        // تعریف سرویس پیامک به صورت Singleton (یکبار ساخته می‌شود و همه جا استفاده می‌شود)
        $this->app->singleton(NegarSmsService::class, function ($app) {
            // خواندن تنظیمات از config/services.php
            $config = config('services.negar_sms');

            // اگر کانفیگ خالی بود (برای اطمینان) مقادیر پیش‌فرض بگذار
            if (empty($config['username'])) {
                $config = [
                    'base_url'   => 'https://negar.armaghan.net',
                    'username'   => 'ehsan',
                    'password'   => 'yP842656',
                    'originator' => '50004644776860'
                ];
            }

            return new NegarSmsService($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }
}
