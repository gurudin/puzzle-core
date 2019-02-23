<?php
use Illuminate\Support\Arr;

/**
 * Package version.
 */
const VERSION = 'dev-master';

if (!function_exists('get_instance')) {
    /**
     * Get the available class instance.
     *
     * @param string className
     *
     * @return mixed|class
     */
    function get_instance(string $className)
    {
        return $className::getInstance();
    }
}

if (!function_exists('get_app')) {
    /**
     * Get the available class.
     *
     * @param string className
     *
     * @return mixed|class
     */
    function get_app(string $className)
    {
        return new $className;
    }
}

if (!function_exists('get_extension')) {
    /**
     * Get all packages installed by composer.
     *
     * @param string $basePath
     * @param string $type (console/api/web)
     *
     * @return array
     */
    function get_extension(string $basePath, string $type = '')
    {
        $filesystem    = get_app('Illuminate\Filesystem\Filesystem');
        $installedPath = $basePath . '/vendor/composer/installed.json';
        $extension     = [];

        if (!$filesystem->exists($installedPath)) {
            return $extension;
        }

        $installed = json_decode($filesystem->get($installedPath), true);
        foreach ($installed as $pageck) {
            if (Arr::get($pageck, 'type') != 'puzzle-package' || empty(Arr::get($pageck, 'extra'))) {
                continue;
            }

            switch ($type) {
                case 'console':
                    if (!empty(Arr::get($pageck, 'extra.puzzle-console'))) {
                        $extension[] = Arr::get($pageck, 'extra.puzzle-console');
                    }
                    break;
                
                default:
                    $extension[] = array_merge(Arr::get($pageck, 'extra'), ['package' => Arr::get($pageck, 'name')]);
                    break;
            }
        }
        unset($pageck);

        return $extension;
    }
}
