<?php

namespace ReviewX\WPDrill\Commands;

use ReviewX\Symfony\Component\Console\Command\Command;
use ReviewX\Symfony\Component\Console\Input\InputInterface;
use ReviewX\Symfony\Component\Console\Output\OutputInterface;
use ReviewX\WPDrill\Plugin;
use ReviewX\WPDrill\Views\ViewManager;
class ViewCacheCommand extends BaseCommand
{
    protected ViewManager $view;
    public function __construct(Plugin $plugin, ?string $name = null)
    {
        $this->view = new ViewManager($plugin);
        parent::__construct($name);
    }
    protected function configure()
    {
        $this->setName('view:cache')->setDescription('Compiled and cache all the twig files')->setHelp('This command allows compiled and cached all the twig files.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('<comment>Compiling and caching all the twig files...</comment>');
        $this->view->compile();
        $output->writeln('<info>Twig files compiled and cached successfully.</info>');
        return Command::SUCCESS;
    }
}
