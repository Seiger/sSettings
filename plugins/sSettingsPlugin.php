<?php
/**
 * Plugin for Seiger advanced settings Management plugin for Evolution CMS admin panel.
 */

use Illuminate\Support\Facades\Event;

/**
 * Add the Tools menu item while keeping the icon markup identical to
 * Evolution Frame::moduleIconHtml() for tabler-backed modules like sArticles.
 */
Event::listen('evolution.OnManagerMenuPrerender', function($params) {
    $title = __('sSettings::global.module_title') !== 'sSettings::global.module_title'
        ? __('sSettings::global.module_title')
        : __('sSettings::global.title');
    $icon = __('sSettings::global.module_icon') !== 'sSettings::global.module_icon'
        ? __('sSettings::global.module_icon')
        : __('sSettings::global.icon');
    $iconSvg = function_exists('svg') && str_starts_with($icon, 'tabler-')
        ? svg($icon)->toHtml()
        : '<i class="' . $icon . '"></i>';
    $iconHtml = '<span class="menu-module-icon" aria-hidden="true">' . $iconSvg . '</span>';

    $menu['ssettings'] = [
        'ssettings',
        'tools',
        $iconHtml . $title,
        'index.php?a=112&id=' . md5($title),
        $title,
        '',
        '',
        'main',
        0,
        6,
    ];

    return serialize(array_merge($params['menu'], $menu));
});
