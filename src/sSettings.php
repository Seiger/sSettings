<?php namespace Seiger\sSettings;
/**
 * Class sSettings - Seiger advanced settings plugin for Evolution CMS admin panel.
 */

use ReflectionClass;

class sSettings
{
    const TYPE_TEXT = 'Text';
    const TYPE_TEXTAREA = 'Textarea';
    const TYPE_TEXTAREA_MINI = 'TextareaMini';
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
        // Generate the base route URL and remove trailing slashes
        $route = route($name);

        // Generate a unique action ID based on the route name
        $a = array_sum(array_map('ord', str_split($name))) + 999;
        $a = $a < 999 ? $a + 999 : $a;

        return str_replace(MODX_MANAGER_URL, '/', $route) . '?a=' . $a;
    }
}
