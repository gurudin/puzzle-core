<?php
namespace Puzzle\Console\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Puzzle\Extension\Console\MigrateCommand;

class Up extends Command
{
    private $paths;

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:up')
            ->setDescription('Upgrades the application by applying new migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = MigrateCommand::getInstance()->history($this->paths);
        $list = [];
        foreach ($migrations as $migration) {
            $list[] = "({$migration['date']}) {$migration['name']}";
        }
        
        $io = new SymfonyStyle($input, $output);
        
        $io->title("A total of " . count($migrations) . " application migrations");
        $io->listing($list);
    }
}
