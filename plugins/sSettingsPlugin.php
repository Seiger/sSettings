<?php
/**
 * Plugin for Seiger advanced settings Management plugin for Evolution CMS admin panel.
 */

use Illuminate\Support\Facades\Event;
use Seiger\sSettings\Facades\sSettings;

/**
 * Add Menu item
 */
Event::listen('evolution.OnManagerMenuPrerender', function($params) {
    $menu['ssettings'] = [
        'ssettings',
        'tools',
        '<i class="'.__('sSettings::global.icon').'"></i><span class="menu-item-text">'.__('sSettings::global.title').'</span>',
        sSettings::route('sSettings.index'),
        __('sSettings::global.title'),
        "",
        "",
        "main",
        0,
        6,
    ];

    return serialize(array_merge($params['menu'], $menu));
});
