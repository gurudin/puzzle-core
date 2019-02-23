<?php
namespace Puzzle\Extension\Console;

use Puzzle\Extension\Extension;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MigrateCommand
{
    /**
     * The current globally available 'local' (if any).
     *
     * @var static
     */
    public static $instance;

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Dispose migrate history.
     */
    public function history(array $paths)
    {
        $migrations     = [];
        $migratePaths[] = $paths['base'] . '/vendor/gurudin/puzzle-core/migrations';
        $extensions     = get_extension($paths['base']);

        foreach ($extensions as $ext) {
            if (!empty(Arr::get($ext, 'package'))) {
                $migratePaths[] = $paths['base']  . '/vendor/' . Arr::get($ext, 'package') . '/migrations';
            }
        }

        $allFiles = Extension::getInstance()->getFiles($migratePaths);
        foreach ($allFiles as $file) {
            $fileArr = \explode("_", $file['name']);
            $migrations[] = [
                'date' => "{$fileArr[0]}-{$fileArr[1]}-{$fileArr[2]} "
                    . substr($fileArr[3], 0, 2) . ":" . substr($fileArr[3], 2, 2) . ":" . substr($fileArr[3], 4, 2),
                'name' => $file['name']
            ];
        }

        $migrations = Collection::make($migrations)->sortByDesc(function ($migration) {
            return $migration['date'];
        })->all();

        return $migrations;
    }
}
