<?php

namespace Neoxia\Routing;

use Illuminate\Support\ServiceProvider;

class ResponseFactoryServiceProvider extends ServiceProvider
{
    /**
     * Register the response factory implementation.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
            return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['redirect']);
        });
    }
}
