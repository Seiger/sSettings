<?php namespace Seiger\sSettings\Controllers;

use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
        if (!evo()->hasPermission('settings', 'mgr')) {
            $message = __('global.access_permission_denied');
            evo()->webAlertAndQuit(is_string($message) ? $message : 'Access permission denied.', "index.php?a=2");
        }

        return $this->view('index', ['activeTab' => 'configure']);
    }

    /**
     * Display render
     *
     * @param string $tpl
     * @param array<string, mixed> $data
     * @return View
     */
    public function view(string $tpl, array $data = []): View
    {
        return ViewFactory::make('sSettings::'.$tpl, $data);
    }
}
