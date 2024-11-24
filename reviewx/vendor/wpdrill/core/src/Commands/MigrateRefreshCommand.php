<?php

namespace Rvx\WPDrill\Commands;

use Rvx\Symfony\Component\Console\Command\Command;
use Rvx\Symfony\Component\Console\Input\InputInterface;
use Rvx\Symfony\Component\Console\Output\OutputInterface;
use Rvx\WPDrill\DB\Migration\Migrator;
class MigrateRefreshCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('db:refresh')->setDescription('Run the database refresh migrations')->setHelp('This command allows you to run the database migrations refresh.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $migrator = new Migrator(WPDRILL_ROOT_PATH . '/database/migrations', $input, $output);
        $migrator->reset();
        $migrator->run();
        return Command::SUCCESS;
    }
}
