<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-07-14 18:52:24
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
    /**
     * publish data
     *
     * @return void
     */
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
