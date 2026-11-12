<?php namespace Seiger\sSettings\Controllers;

use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Seiger\sSettings\Support\ManagerSettingsAccess;

/** Renders the sSettings module for managers with the settings permission. */
class sSettingsController
{
    /**
     * Show tabs with custom system settings
     *
     * @return View
     */
    public function index(): View
    {
        $request = request();
        $activeTab = $request instanceof Request ? (string) $request->get('tab', 'settings') : 'settings';

        return $this->view('index', ['activeTab' => $activeTab]);
    }

    /**
     * Show tab with configuration fields
     *
     * @return View
     */
    public function configure(): View
    {
        return $this->view('index', ['activeTab' => 'configure']);
    }

    /**
     * Check the manager's current settings grant before building a module view.
     *
     * Both HTTP routes and the file module render through this method.
     *
     * @param string $tpl
     * @param array<string, mixed> $data
     * @return View
     */
    public function view(string $tpl, array $data = []): View
    {
        if (!ManagerSettingsAccess::allows()) {
            $message = __('global.access_permission_denied');
            evo()->webAlertAndQuit(is_string($message) ? $message : 'Access permission denied.', 'index.php?a=2');
        }

        return ViewFactory::make('sSettings::'.$tpl, $data);
    }
}
