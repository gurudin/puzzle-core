<?php
namespace Puzzle\Console\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Puzzle\Extension\Console\MigrateCommand;

class History extends Command
{
    private $paths;

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:history')
            ->setDescription('Displays the migration history.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = MigrateCommand::getInstance()->history($this->paths);

        $output->writeln("");
        $output->writeln("<comment>A total of " . count($migrations) . " application migrations</comment>");
        foreach ($migrations as $migration) {
            $output->writeln("\t({$migration['date']}) {$migration['name']}");
        }
    }
}
