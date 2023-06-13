<?php namespace Seiger\sSettings;
/**
 * Class sSettings - Seiger advanced settings plugin for Evolution CMS admin panel.
 */

class sSettings
{
    /**
     * Get url from route name
     *
     * @param string $name Route name
     * @return string
     */
    public function route(string $name): string
    {
        return str_ireplace(evo()->getConfig('friendly_url_suffix', ''), '', route($name));
    }
}
