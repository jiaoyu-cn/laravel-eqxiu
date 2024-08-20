<?php

namespace Githen\LaravelEqxiu\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Githen\LaravelEqxiu\Client;

/**
 * 自动注册为服务
 */
class EqxiuServiceProvider extends LaravelServiceProvider
{
    /**
     * 启动服务
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/config.php' => config_path('eqxiu.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('jiaoyu.eqxiu', function ($app) {
            return new Client([
                'app_id' => $app['config']->get('eqxiu.app_id'),
                'app_key' => $app['config']->get('eqxiu.app_key'),
            ]);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('jiaoyu.eqxiu');
    }

}
