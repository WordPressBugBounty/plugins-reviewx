<?php

namespace Rvx\WPDrill\Commands;

use Rvx\Symfony\Component\Console\Command\Command;
use Rvx\Symfony\Component\Console\Helper\Table;
use Rvx\Symfony\Component\Console\Helper\TableSeparator;
use Rvx\Symfony\Component\Console\Input\InputInterface;
use Rvx\Symfony\Component\Console\Output\OutputInterface;
use Rvx\WPDrill\DB\Migration\Migrator;
use Rvx\WPDrill\Facades\Config;
class PluginInfoCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('plugin:info')->setDescription('Display the plugin information')->setHelp('This command allows you to display the plugin information.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $config = Config::get('plugin');
        $table = new Table($output);
        $table->setRows([['<comment>Name</comment>', $config['name']], new TableSeparator(), ['<comment>Version</comment>', $config['version']]]);
        $table->render();
        return Command::SUCCESS;
    }
}
