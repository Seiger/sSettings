<?php namespace Seiger\sSettings;
/**
 * Class sSettings - Seiger advanced settings plugin for Evolution CMS admin panel.
 */

use ReflectionClass;

class sSettings
{
    const TYPE_TEXT = 'Text';
    const TYPE_TEXTAREA = 'Textarea';
    const TYPE_IMAGE = 'Image';
    const TYPE_CHECKBOX = 'Checkbox';

    /**
     * Return list of type fields and labels
     *
     * @return array
     */
    public function listType(): array
    {
        $list = [];
        $class = new ReflectionClass(__CLASS__);
        foreach ($class->getConstants() as $constant => $value) {
            if (str_starts_with($constant, 'TYPE_')) {
                $const = strtolower($constant);
                $list[strtolower($value)] = $value;
            }
        }
        return $list;
    }

    /**
     * Get url from route name with action id
     *
     * @param string $name Route name
     * @return string
     */
    public function route(string $name): string
    {
        $route = rtrim(route($name), '/');
        if (evo()->getConfig('friendly_url_suffix', '') != '/') {
            $route = str_ireplace(evo()->getConfig('friendly_url_suffix', ''), '', route($name));
        }

        $a = 0;
        $arr = str_split($name, 1);
        foreach ($arr as $n) {
            $a += ord($n);
        }
        $a = $a < 999 ? $a + 999 : $a;

        return $route.'?a='.$a;
    }
}
