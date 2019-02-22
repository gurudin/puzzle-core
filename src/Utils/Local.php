<?php
namespace Puzzle\Utils;

class Local
{
    /**
     * The current globally available 'local' (if any).
     *
     * @var static
     */
    public static $instance;

    private function __construct() {}

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

    public function getClasses()
    {

    }

    public function formPaths(string $defaultPath = '')
    {
        echo $defaultPath;
    }
}
