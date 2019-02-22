<?php
namespace Puzzle\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Puzzle\Console\Migrate;
use RegexIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Puzzle\Utils\Local;

class Console extends Command
{
    /**
     * 'Local' class instance.
     */
    private $local;

    public function __construct()
    {
        $this->local = get_instance(Local::class);
    }


    public function listen()
    {
        $this->local->formPaths('cxc');
        return;
        $path = __DIR__; // 比如他是约定好目录
        $classes = array();
        
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $classes[] = $namespace . '\\' . $tokens[$index][1];
                }
            }
        }
        print_r($classes);
        // $app = new Application('puzzle', 'dev-master');
        // print_r(get_declared_classes());
        // $application->add(new Create());
        // $application->run();
        // get_called_class();
    }
}
