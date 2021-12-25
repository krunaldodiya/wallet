<?php

namespace KD\Wallet;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        // $this->loadRoutesFrom(__DIR__ . '/routes');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/wallet.php', 'wallet');

        $this->app->singleton('wallet', function () {
            return new Wallet;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['wallet'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__. '/../../config/wallet.php' => config_path('wallet.php'),
        ], 'wallet.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/kd'),
        ], 'wallet.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/kd'),
        ], 'wallet.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/kd'),
        ], 'wallet.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
