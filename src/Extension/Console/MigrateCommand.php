<?php
namespace Puzzle\Extension\Console;

use Puzzle\Extension\Extension;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MigrateCommand
{
    const MIGRATE_CREATE = 'create';
    const MIGRATE_UPDATE = 'update';

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
     *
     * @param array $paths
     *
     * @return array
     */
    public function history(array $paths)
    {
        $migrations     = [];
        $extensions     = get_extension($paths['base']);
        $migratePaths   = [
            $paths['base'] . '/vendor/gurudin/puzzle-core/migrations',
            $paths['base'] . '/resources/migrations'
        ];

        foreach ($extensions as $ext) {
            if (!empty(Arr::get($ext, 'package'))) {
                $migratePaths[] = $paths['base']  . '/vendor/' . Arr::get($ext, 'package') . '/migrations';
            }
        }

        $allFiles = Extension::getInstance()->getFiles($migratePaths);
        foreach ($allFiles as $file) {
            $fileArr = \explode("_", $file['name']);
            if (count($fileArr) < 4) {
                continue;
            }

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

    /**
     * Dispose migrate save (create/update).
     *
     * @param string $migrateType
     * @param string $basePath
     * @param string $name
     * @param string $package
     *
     * @return array ['status' => true|false, 'msg' => '', 'name' => '', 'fullPath' => '']
     */
    public function save(string $migrateType, string $basePath, string $name, string $package)
    {
        $filesystem = get_app('Illuminate\Filesystem\Filesystem');

        $targetPath = $package == ''
            ? $basePath . '/resources'
            : $basePath . '/vendor/' . $package;

        $migrationPath = $targetPath . '/migrations';

        if (!$filesystem->exists($targetPath)) {
            return [
                'status' => false,
                'msg' => 'Directory does not exist',
                'fullPath' => $targetPath
            ];
        }

        if (!$filesystem->exists($migrationPath)) {
            if (!$filesystem->makeDirectory($migrationPath, 0775)) {
                return [
                    'status' => false,
                    'msg' => 'Directory creation failed',
                    'fullPath' => $migrationPath
                ];
            }
        }

        $fileName = date('Y_m_d_His') . '_' . $name . '.php';
        $path     = $migrationPath . '/' . $fileName;
        $stub     = $filesystem->get(__DIR__ . '/../../Stubs/migrations/' . $migrateType . '.stub');
        
        $filesystem->put($path, $stub);

        return ['status' => true, 'msg' => 'Created migration', 'name' => $fileName, 'fullPath' => $migrationPath];
    }
}
