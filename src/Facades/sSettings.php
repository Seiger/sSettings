<?php namespace Seiger\sSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class sSettings
 *
 * @package Seiger\sSettings
 * @mixin \Seiger\sSettings\sSettings
 */
class sSettings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sSettings';
    }
}