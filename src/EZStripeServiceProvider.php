<?php

namespace bkilshaw\EZStripe;

use Illuminate\Support\ServiceProvider;

class EZStripeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bkilshaw');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'bkilshaw');

        $this->loadRoutesFrom(__DIR__.'/routes.php');


        $this->loadViewComponentsAs('ezstripe', [Components\Javascript::class]);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ezstripe.php', 'ezstripe');

        // Register the service the package provides.
        $this->app->singleton('ezstripe', function ($app) {
            return new EZStripe;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['ezstripe'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/ezstripe.php' => config_path('ezstripe.php'),
        ], 'ezstripe.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/bkilshaw'),
        ], 'ezstripe.views');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/bkilshaw'),
        ], 'ezstripe.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/bkilshaw'),
        ], 'ezstripe.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
