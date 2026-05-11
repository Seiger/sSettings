<?php namespace Seiger\sSettings\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class sSettingsController
{
    /**
     * Show tabs with custom system settings
     *
     * @return View
     */
    public function index(): View
    {
        return $this->view('index', ['activeTab' => (string) request()->get('tab', 'settings')]);
    }

    /**
     * Show tab with configuration fields
     *
     * @return View
     */
    public function configure(): View
    {
        if (!evo()->hasPermission('settings', 'mgr')) {
            evo()->webAlertAndQuit(__('global.access_permission_denied'), "index.php?a=2");
        }

        return $this->view('index', ['activeTab' => 'configure']);
    }

    /**
     * Serve manager assets from the package during symlinked local development.
     *
     * @param string $file
     * @return Response
     */
    public function asset(string $file): Response
    {
        if ($file !== 'ssettings.css') {
            abort(404);
        }

        $path = dirname(__DIR__, 2) . '/assets/' . $file;
        if (!is_file($path)) {
            abort(404);
        }

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
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
        $data['sSettingsAssetVersion'] = filemtime(dirname(__DIR__, 2) . '/assets/ssettings.css');

        return \View::make('sSettings::'.$tpl, $data);
    }
}
