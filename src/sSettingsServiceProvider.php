<?php namespace Seiger\sSettings;

use EvolutionCMS\ServiceProvider;

class sSettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Only Manager
        if (IN_MANAGER_MODE) {
            // Add custom routes for package
            include(__DIR__.'/Http/routes.php');

            // Views
            $this->loadViewsFrom(dirname(__DIR__) . '/views', 'sSettings');

            // MultiLang
            $this->loadTranslationsFrom(dirname(__DIR__) . '/lang', 'sSettings');

            // For use config
            $this->publishes([
                dirname(__DIR__) . '/config/sSettingsAlias.php' => config_path('app/aliases/sSettings.php', true),
                dirname(__DIR__) . '/config/sSettingsSettings.php' => config_path('seiger/settings/sSettings.php', true),
                dirname(__DIR__) . '/images/seigerit-yellow.svg' => public_path('assets/site/seigerit-yellow.svg'),
            ]);
        }

        $this->app->singleton(sSettings::class);
        $this->app->alias(sSettings::class, 'sSettings');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Add plugins to Evo
        $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');
    }
}
