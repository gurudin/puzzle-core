<?php
namespace Puzzle\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Puzzle\Extension\Extension;

class Console extends Command
{
    /**
     * @var array
     *
     * Directory
     */
    private $paths = [];

    /**
     * @var class
     *
     * 'Puzzle\Extension\Extension' class instance.
     */
    private $extension;

    /**
     * @var array
     *
     * Need to load files.
     */
    private $loadFiles = [];

    public function __construct(array $paths)
    {
        $this->paths = $paths;
        $this->extension = get_instance(Extension::class);
    }


    public function listen()
    {
        $application = new Application('puzzle', VERSION);

        foreach ($this->getCommands() as $class) {
            $application->add(new $class);
        }

        $application->run();
    }

    private function getCommands()
    {
        $classes = $this->extension->getClasses($this->fromPaths());
        foreach ($classes as $inx => $class) {
            if ($class == __CLASS__) {
                unset($classes[$inx]);
            }
        }

        return $classes;
    }

    private function fromPaths()
    {
        return $this->extension->fromPaths(array_merge($this->paths, ['console' => __DIR__]));
    }
}
