<?php

namespace KD\Wallet;

use Illuminate\Support\ServiceProvider;
use KD\Wallet\Models\Wallet;

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
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'wallet');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'wallet');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->loadRoutesFrom(__DIR__ . '/routes');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/wallet.php' => config_path('wallet.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/wallet'),
            ], 'views');
            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/wallet'),
            ], 'assets');
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/wallet'),
            ], 'lang');
            // Registering package commands.
            // $this->commands([]);
        }
    }
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/wallet.php', 'wallet');
        $this->app->singleton('wallet', function () {
            return new Wallet;
        });
    }
}
