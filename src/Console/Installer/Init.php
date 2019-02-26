<?php
namespace Puzzle\Console\Installer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Puzzle\Extension\Console\InstallerCommand;

class Init extends Command
{
    private $paths;

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('init')
            ->setAliases(['install'])
            ->setDescription('Select the environment to initialize the application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $environments = InstallerCommand::getInstance()->getEnvironments($this->paths['base']);
        
        $io = new SymfonyStyle($input, $output);
        
        if (!$environments['status']) {
            $io->error(["Fatal error: {$environments['msg']}", "Full Path: {$environments['path']}"]);
            return false;
        }

        $io->section("Puzzle<info>(" . VERSION . ")</info> Application Initialization");
        $io->text("Which environment do you want the application to be initialized in?");
        $io->listing($environments['dirs']);

        // Choice environment.
        $envChoose = $io->ask('Your choice [0-' . (count($environments['dirs']) - 1) . ', or "q" to quit]');
        if (in_array($envChoose, ['q', 'quit']) || $envChoose == '') {
            $io->text("Quit initialization.");
            return false;
        }
        if ($envChoose < 0 || $envChoose > (count($environments['dirs']) -1)) {
            $io->warning("The wrong choice.");
            return false;
        }

        // Confirm.
        if (!$io->confirm(
            "Initialize the application under '{$environments['envs'][$envChoose]['env']}' environment? [yes|no]",
            true
        )) {
            $io->text("Quit initialization.");
            return false;
        }

        $io->text("Start initialization ...");
        $io->newLine();

        $result = InstallerCommand::getInstance()->init(
            $environments['envs'][$envChoose]['path'],
            $this->paths['base']
        );

        if ($result['status']) {
            $io->text($result['data']);
            $io->newLine();
            $io->text("... initialization completed.");
        } else {
            $io->error("Fatal error: {$result['msg']}");
        }
    }
}
