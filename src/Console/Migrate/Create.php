<?php
namespace Puzzle\Console\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:create')
            ->setDescription('Creates a new migration.');
            // ->setAliases(['aa']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("migrate:create");
    }
}
