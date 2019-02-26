<?php
namespace Puzzle\Extension\Console;

use Puzzle\Extension\Extension;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class InstallerCommand
{
    /**
     * The current globally available 'local' (if any).
     *
     * @var static
     */
    public static $instance;

    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    private function __construct()
    {
        $this->filesystem = get_app('Illuminate\Filesystem\Filesystem');
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
     * Get environments to initialize.
     *
     * @param string $basePath
     *
     * @return array
     */
    public function getEnvironments(string $basePath)
    {
        $this->filesystem = get_app('Illuminate\Filesystem\Filesystem');

        $environmentPath = $basePath . '/resources/environments';
        if (!$this->filesystem->exists($environmentPath)
            || empty($directories = $this->filesystem->directories($environmentPath))
        ) {
            return ['status' => false, 'msg' => 'Directory does not exist', 'path' => $environmentPath];
        }

        $envs = $dirs = [];
        foreach ($directories as $inx => $dir) {
            $disName = Str::ucfirst($this->filesystem->basename($dir));
            $envs[] = [
                'env'  => $disName,
                'path' => $dir
            ];
            $dirs[] = "[{$inx}] " . $disName;
        }

        return ['status' => true, 'dirs' => $dirs, 'envs' => $envs];
    }

    /**
     * Initialization
     *
     * @param string $envPath
     * @param string $basePath
     *
     * @return array
     */
    public function init(string $envPath, string $basePath)
    {
        $filesLogs = $this->copyFilesToConfig($envPath, $basePath);
        if ($filesLogs === false) {
            return ['status' => false, 'msg' => 'Copy the failure'];
        }

        $chmodLogs = $this->addFilesPermission($basePath);

        return ['status' => true, 'data' => array_merge($filesLogs, [" "], $chmodLogs)];
    }

    /**
     * Add files permissions.
     *
     * @param string $basePath
     *
     * @return array Logs
     */
    private function addFilesPermission(string $basePath)
    {
        exec("chmod -R 0777 {$basePath}/public/assets");
        exec("chmod -R 0777 {$basePath}/runtime");
        exec("chmod -R 0755 {$basePath}/puzzle");
        exec("chmod -R 0755 {$basePath}/resources");
        exec("chmod -R 0755 {$basePath}/public");

        return [
            "    chmod 0777 puzzle/public/assets",
            "    chmod 0777 puzzle/runtime",
            "    chmod 0755 puzzle/puzzle",
            "    chmod 0755 puzzle/resources",
            "    chmod 0755 puzzle/public",
        ];
    }

    /**
     * Copy files to config.
     *
     * @param string $envPath
     * @param string $basePath
     *
     * @return array Logs
     */
    private function copyFilesToConfig(string $envPath, string $basePath)
    {
        if (!$this->filesystem->copyDirectory($envPath, $basePath . '/config')) {
            return false;
        }

        $pathArr = explode('resources', $envPath);
        
        $files = $this->filesystem->allFiles($envPath, true);

        $logs = [];
        foreach ($files as $file) {
            $logs[] = 'overwrite resources' . $pathArr[1] . '/' . $file->getRelativePathname();
        }

        return $logs;
    }
}
