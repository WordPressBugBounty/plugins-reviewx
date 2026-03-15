<?php

namespace ReviewX\WPDrill\Commands;

use ReviewX\Symfony\Component\Console\Command\Command;
use ReviewX\Symfony\Component\Console\Input\InputInterface;
use ReviewX\Symfony\Component\Console\Output\OutputInterface;
use ReviewX\WPDrill\DB\Migration\Migrator;
class MigrateResetCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('db:reset')->setDescription('Run the database reset migrations')->setHelp('This command allows you to run the database migrations reset.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $migrator = new Migrator(WPDRILL_ROOT_PATH . '/database/migrations', $input, $output);
        $migrator->reset();
        return Command::SUCCESS;
    }
}
