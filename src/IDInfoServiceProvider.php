<?php

namespace Ouhaohan8023\IDInfo;

use Illuminate\Support\ServiceProvider;
use Ouhaohan8023\IDInfo\Console\IDInfoInit;
use Ouhaohan8023\IDInfo\Facade\IDInfoFacade;
use Ouhaohan8023\IDInfo\Model\IDInfo;

class IDInfoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/Database/migrations' => database_path('migrations'),
                __DIR__.'/Database/seeds' => database_path('seeders'),
            ],
            'config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                IDInfoInit::class,
            ]);
        }

    }

    public function register()
    {
        $this->app->singleton('IDInfo', function () {
            return new IDInfo;
        });

        // 注册 Facade 别名
        $this->app->alias('IDInfo', IDInfoFacade::class);
    }
}
