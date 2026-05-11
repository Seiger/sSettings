<?php namespace Seiger\sSettings;

use EvolutionCMS\ServiceProvider;
use Livewire\Livewire;

class sSettingsServiceProvider extends ServiceProvider
{
    protected string $root;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->root = dirname(__DIR__);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // MultiLang
        $this->loadTranslationsFrom($this->root . '/lang', 'sSettings');

        // Check sSettings
        $this->mergeConfigFrom($this->root . '/config/sSettingsCheck.php', 'cms.settings');

        // Class alias
        $this->app->singleton(sSettings::class);
        $this->app->alias(sSettings::class, 'sSettings');

        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE) {
            $this->bootManager();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Add plugins to Evo
        $this->loadPluginsFrom($this->root . '/plugins/');

        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE) {
            $lang = 'en';
            if (isset($_SESSION['mgrUsrConfigSet']['manager_language'])) {
                $lang = (string) $_SESSION['mgrUsrConfigSet']['manager_language'];
            } elseif (is_file(evo()->getSiteCacheFilePath())) {
                $siteCache = file_get_contents(evo()->getSiteCacheFilePath());
                preg_match('@\$c\[\'manager_language\'\]="\w+@i', $siteCache, $matches);
                if (count($matches)) {
                    $lang = str_replace('$c[\'manager_language\']="', '', $matches[0]);
                }
            }

            $langFile = $this->root . '/lang/' . $lang . '/global.php';
            $labels = is_file($langFile) ? include $langFile : include $this->root . '/lang/en/global.php';

            $this->app->registerModule(
                $labels['module_title'] ?? $labels['title'],
                $this->root . '/module/sSettingsModule.php',
                $labels['module_icon'] ?? $labels['icon'],
                ['hidden' => true]
            );
        }
    }

    protected function bootManager(): void
    {
        // Add custom routes for package
        include($this->root . '/src/Http/routes.php');

        // Views
        $this->loadViewsFrom($this->root . '/views', 'sSettings');

        // Files
        $this->publishes([
            $this->root . '/config/sSettingsAlias.php' => config_path('app/aliases/sSettings.php', true),
            $this->root . '/config/sSettingsSettings.php' => config_path('seiger/settings/sSettings.php', true),
            $this->root . '/images/seigerit-blue.svg' => public_path('assets/site/seigerit-blue.svg'),
            $this->root . '/assets/ssettings.css' => public_path('assets/modules/ssettings/ssettings.css'),
        ]);

        Livewire::component('ssettings.module-panel', \Seiger\sSettings\Livewire\ModulePanel::class);
        Livewire::component('ssettings.settings-panel', \Seiger\sSettings\Livewire\SettingsPanel::class);
        Livewire::component('ssettings.configure-panel', \Seiger\sSettings\Livewire\ConfigurePanel::class);
    }
}
