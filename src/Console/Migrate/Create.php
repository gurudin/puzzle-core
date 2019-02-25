<?php
namespace Puzzle\Console\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Puzzle\Extension\Console\MigrateCommand;

class Create extends Command
{
    private $paths;

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:create')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the migration.'
            )
            ->addArgument(
                'package',
                InputArgument::OPTIONAL,
                'The package name of the migration.'
            )
            ->setDescription('Creates a new migration.');
            // ->setAliases(['aa']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name    = $input->getArgument('name');
        $package = $input->getArgument('package');
        
        $result = MigrateCommand::getInstance()->save(
            MigrateCommand::getInstance()::MIGRATE_CREATE,
            $this->paths['base'],
            $name,
            $package === null ? '' : $package
        );

        $io = new SymfonyStyle($input, $output);
        if ($result['status']) {
            $output->writeln("");
            $output->writeln("<info>{$result['msg']}: {$result['name']}</info>");
            $output->writeln("<info>Full path: {$result['fullPath']}</info>");
        } else {
            $io->error("Fatal error: {$result['msg']} '{$result['fullPath']}'");
        }
    }
}
