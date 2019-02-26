<?php
namespace Puzzle\Extension\Console;

use Puzzle\Extension\Extension;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Connectors\ConnectionFactory;

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
     * @var Illuminate\Database\Migrations\MigrationCreator
     */
    private $creator;

    private function __construct()
    {
        $this->creator = new MigrationCreator(get_app('Illuminate\Filesystem\Filesystem'));
    }

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
     * @param bool $isFullPaths
     *
     * @return array
     */
    public function history(array $paths, bool $isFullPaths = false)
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
        if ($isFullPaths) {
            foreach ($allFiles as $inx => $file) {
                $fileArr = \explode("_", $file['name']);
                if (count($fileArr) < 4) {
                    unset($allFiles[$inx]);
                }
            }
            return array_values($allFiles);
        }

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
        $targetPath = $package == ''
            ? $basePath . '/resources'
            : $basePath . '/vendor/' . $package;

        $migrationPath = $targetPath . '/migrations';

        if (!$this->creator->getFilesystem()->exists($targetPath)) {
            return [
                'status' => false,
                'msg' => 'Directory does not exist',
                'fullPath' => $targetPath
            ];
        }

        if (!$this->creator->getFilesystem()->exists($migrationPath)) {
            if (!$this->creator->getFilesystem()->makeDirectory($migrationPath, 0775)) {
                return [
                    'status' => false,
                    'msg' => 'Directory creation failed',
                    'fullPath' => $migrationPath
                ];
            }
        }
        
        $path = $this->creator->create(
            $name,
            $migrationPath,
            '{{table}}',
            $migrateType == self::MIGRATE_CREATE ? true : false
        );

        return [
            'status' => true,
            'msg' => 'Created migration',
            'name' => $this->creator->getFilesystem()->basename($path),
            'fullPath' => $migrationPath
        ];
    }

    /**
     * Dispose migrate upgrades.
     *
     * @param array $paths
     * @param string $name
     *
     * @return array
     */
    public function up(array $paths, string $name)
    {
        $migrations = $this->history($paths, true);

        $conf = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'puzzle',
            'username' => 'root',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        $capsule = new Capsule();

        $capsule->addConnection($conf);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        
        
        $connectionResolver = new ConnectionResolver();
        $connectionResolver->addConnection('default', Capsule::connection());
        $connectionResolver->setDefaultConnection('default');

        $migrator = new Migrator(
            new DatabaseMigrationRepository($connectionResolver, 'migrations'),
            $connectionResolver,
            $this->creator->getFilesystem()
        );
        
        foreach ($migrations = array_column($migrations, 'path') as $path) {
            $this->creator->getFilesystem()->requireOnce($path);
        }
        
        
        print_r($migrator->runPending($migrations));
    }
}
