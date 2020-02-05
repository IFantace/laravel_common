<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-02-05 20:06:43
 */

namespace Ifantace\Common;

use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Schema::defaultStringLength(191);
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'Common');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'Common');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPublishables();
    }
    private function registerPublishables()
    {
        $basePath = __DIR__;
        $arrPublishable = [
            'migrations' => [
                "$basePath/publishable/databases/migrations" => database_path('migrations'),
            ],
            'config' => [
                "$basePath/publishable/config" => config_path(),
            ],
        ];
        foreach ($arrPublishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
        // $this->mergeConfigFrom(
        //     __DIR__ . '/path/to/config/courier.php',
        //     'courier'
        // );
    }
}
