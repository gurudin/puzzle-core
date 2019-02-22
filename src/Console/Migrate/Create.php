<?php
namespace Puzzle\Console\Migrate;

use Puzzle\Console\Console;

class Create extends Console
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:create')
            ->setDescription('Creates a new migration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("migrate:create");
    }
}
