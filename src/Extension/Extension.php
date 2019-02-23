<?php
namespace Puzzle\Extension;

use Illuminate\Support\Arr;
use RegexIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Extension
{
    const CONSOLE_EXT = 'console';
    const API_EXT = 'api';

    /**
     * The current globally available 'local' (if any).
     *
     * @var static
     */
    public static $instance;

    private function __construct()
    {
        # Code:
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
     * Returns all classes in the directory.
     *
     * @param array $paths
     *
     * @return array
     */
    public function getFiles(array $paths)
    {
        $files = [];

        if (empty($paths)) {
            return $files;
        }

        $filesystem = get_app('Illuminate\Filesystem\Filesystem');

        foreach ($paths as $path) {
            if (!$filesystem->exists($path)) {
                continue;
            }

            $allFiles = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($allFiles as $file) {
                $files[] = ['name' => $file->getFileName(), 'path' => $file->getRealPath()];
            }
        }

        return $files;
    }

    /**
     * Returns all classes in the directory.
     *
     * @param array $paths
     *
     * @return array
     */
    public function getClasses(array $paths)
    {
        $classes = [];

        if (empty($paths)) {
            return $classes;
        }

        $filesystem = get_app('Illuminate\Filesystem\Filesystem');

        foreach ($paths as $path) {
            if (!$filesystem->exists($path)) {
                continue;
            }

            $allFiles = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            $phpFiles = new RegexIterator($allFiles, '/\.php$/');

            foreach ($phpFiles as $phpFile) {
                $content = $filesystem->get($phpFile->getRealPath());
                $tokens = token_get_all($content);

                $namespace = '';
                for ($i = 0; isset($tokens[$i]); $i++) {
                    if (!isset($tokens[$i][0])) {
                        continue;
                    }

                    if (T_NAMESPACE === $tokens[$i][0]) {
                        $i += 2; // Skip namespace keyword and whitespace
                        while (isset($tokens[$i]) && is_array($tokens[$i])) {
                            $namespace .= $tokens[$i++][1];
                        }
                    }
                    if (T_CLASS === $tokens[$i][0]) {
                        $i += 2; // Skip class keyword and whitespace
                        if (isset($tokens[$i][1])) {
                            $classes[] = $namespace . '\\' . $tokens[$i][1];
                        }
                    }
                }
            }
        }
        unset($path);

        return $classes;
    }

    /**
     * Get from Paths.
     *
     * @param array $paths
     *
     * @return array
     */
    public function fromPaths(array $paths)
    {
        $fromPaths = $this->extensionPaths($paths['base']);

        if (!empty(Arr::get($paths, 'console'))) {
            $fromPaths = array_merge($fromPaths, [$paths['console']]);
        }

        return $fromPaths;
    }

    /**
     * Get extension paths.
     *
     * @param string $basePath
     *
     * @return array Extension paths
     */
    private function extensionPaths(string $basePath)
    {
        $paths      = [];
        $extensions = get_extension($basePath, self::CONSOLE_EXT);

        foreach ($extensions as $extension) {
            $src = Arr::get($extension, 'src');
            if (empty($src)) {
                continue;
            }

            $paths[] = $basePath . '/vendor/' . $src;
        }
        unset($extension);

        return $paths;
    }
}
