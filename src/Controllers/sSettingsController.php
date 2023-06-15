<?php namespace Seiger\sSettings\Controllers;

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class sSettingsController
{
    //public $url;

    /**
     * Construct
     */
    public function __construct()
    {
        //$this->url = $this->moduleUrl();
        //Paginator::defaultView('pagination');
    }

    /**
     * Show tabs with custom system settings
     *
     * @return View
     */
    public function index(): View
    {
        // TODO Refactor after some times
        if (file_exists(EVO_CORE_PATH . 'custom/config/cms/settings/sSettings.php')) {
            copy(EVO_CORE_PATH . 'custom/config/cms/settings/sSettings.php', EVO_CORE_PATH . 'custom/config/seiger/settings/sSettings.php');
            unlink(EVO_CORE_PATH . 'custom/config/cms/settings/sSettings.php');
        }

        $tabs = evo()->getConfig('sSettings', ["basicTab"=>["label"=>"","fields"=>[]]]);
        return $this->view('index', ['tabs' => $tabs]);
    }

    public function updateSettings()
    {
        $settings = request()->all();
        if ($settings && is_array($settings) && count($settings)) {
            foreach ($_POST as $key => $value) {
                if (str_starts_with($key, 'sset_') && is_scalar($value)) {
                    $setting = SystemSetting::whereSettingName($key)->firstOrCreate();
                    $setting->setting_name = $key;
                    $setting->setting_value = $value;
                    $setting->save();
                    evo()->setConfig($key, $value);
                }
            }

            evo()->clearCache('full');
        }

        return redirect()->back();
    }

    /**
     * Show tab with configuration fields
     *
     * @return View
     */
    public function configure(): View
    {
        $tabs = evo()->getConfig('sSettings', ["basicTab"=>["label"=>"","fields"=>[]]]);
        return $this->view('configure', ['tabs' => $tabs]);
    }

    /**
     * Update settings configuration
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateConfigure()
    {
        $tabs = request()->get('tabs');
        if ($tabs && is_array($tabs) && count($tabs)) {
            $lang = include_once dirname(__DIR__) . '/../lang/' . evo()->getLocale() . '/global.php';
            if ($lang && is_array($lang) && count($lang)) {
                $lang = array_flip($lang);
            }

            $i = 1;
            $presentSettings = [];

            $string = '<?php return [' . "\n";

            foreach ($tabs as $key => $tab) {
                // Tab key
                if (Str::of($key)->startsWith('tabId')) {
                    continue;
                } elseif (Str::of($key)->startsWith('newTab')) {
                    if (isset($tab['label']) && trim($tab['label'])) {
                        $key = Str::slug($tab['label'], '_', 'en');
                    } else {
                        $key = 'newTab' . $i;
                    }
                }
                $string .= "\t" . '"' . $key . '" => [' . "\n";

                // Tab label
                $tabLabel = '"label" => "",';
                if (isset($tab['label']) && trim($tab['label'])) {
                    if (isset($lang[$tab['label']])) {
                        $tabLabel = '"label" => "sSettings::global.' . $lang[$tab['label']] . '",';
                    } else {
                        $tabLabel = '"label" => "' . $tab['label'] . '",';
                    }
                }
                $string .= "\t\t" . $tabLabel . "\n";

                // Tab fields
                $string .= "\t\t" . '"fields" => [' . "\n";
                if ($tab['fields'] && is_array($tab['fields']) && count($tab['fields'])) {
                    foreach ($tab['fields'] as $field) {
                        if (isset($field['name']) && trim($field['name'])) {
                            $string .= "\t\t\t" . '[' . "\n";

                            // Field name
                            $string .= "\t\t\t\t" . '"name" => "' . Str::slug($field['name'], '_', 'en') . '",' . "\n";
                            $presentSettings[] = 'sset_' . Str::slug($field['name'], '_', 'en');

                            // Field label
                            $fieldLabel = '"label" => "",';
                            if (isset($field['label']) && trim($field['label'])) {
                                if (isset($lang[$field['label']])) {
                                    $fieldLabel = '"label" => "sSettings::global.' . $lang[$field['label']] . '",';
                                } else {
                                    $fieldLabel = '"label" => "' . $field['label'] . '",';
                                }
                            }
                            $string .= "\t\t\t\t" . $fieldLabel . "\n";

                            // Field description
                            $fieldDescription = '"description" => "",';
                            if (isset($field['description']) && trim($field['description'])) {
                                if (isset($lang[$field['description']])) {
                                    $fieldDescription = '"description" => "sSettings::global.' . $lang[$field['description']] . '",';
                                } else {
                                    $fieldDescription = '"description" => "' . $field['description'] . '",';
                                }
                            }
                            $string .= "\t\t\t\t" . $fieldDescription . "\n";

                            // Field type
                            $fieldType = '"type" => "text",';
                            if (isset($field['type']) && trim($field['type'])) {
                                $fieldType = '"type" => "' . Str::slug($field['type'], '', 'en') . '",';
                            }
                            $string .= "\t\t\t\t" . $fieldType . "\n";

                            $string .= "\t\t\t" . '],' . "\n";
                        }
                    }
                }
                $string .= "\t\t" . ']' . "\n";

                // Tab key close
                $string .= "\t" . '],' . "\n";
            }
            $string .= '];';

            // Save config
            $handle = fopen(EVO_CORE_PATH . 'seiger/config/seiger/settings/sSettings.php', "w");
            fwrite($handle, $string);
            fclose($handle);

            // Synchronize system settings
            $allSettings = SystemSetting::where('setting_name', 'like', 'sset_%')->get()->pluck('setting_name')->toArray();

            $deleteSettings = array_diff($allSettings, $presentSettings);
            if (count($deleteSettings)) {
                foreach ($deleteSettings as $deleteSetting) {
                    SystemSetting::whereSettingName($deleteSetting)->delete();
                }
            }

            $addSettings = array_diff($presentSettings, $allSettings);
            if (count($addSettings)) {
                foreach ($addSettings as $addSetting) {
                    $setting = SystemSetting::whereSettingName($addSetting)->firstOrCreate();
                    $setting->setting_name = $addSetting;
                    $setting->save();
                }
            }

            evo()->clearCache('full');
        }

        return redirect()->back();
    }

    /**
     * Display render
     *
     * @param string $tpl
     * @param array $data
     * @return bool
     */
    public function view(string $tpl, array $data = []): View
    {
        return \View::make('sSettings::'.$tpl, $data);
    }
}
