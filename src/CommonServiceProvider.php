<?php

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

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php' .
            '');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'HelloWorld');
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
                "$basePath/publishable/config/common.php" => config_path('common.php'),
            ],
        ];

        foreach ($arrPublishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
