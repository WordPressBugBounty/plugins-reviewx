<?php

namespace Rvx\WPDrill\Commands;

use Rvx\Symfony\Component\Console\Command\Command;
use Rvx\Symfony\Component\Console\Helper\Table;
use Rvx\Symfony\Component\Console\Input\InputInterface;
use Rvx\Symfony\Component\Console\Output\OutputInterface;
class PluginSetupCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('plugin:init')->setDescription('Setup the plugin')->setHelp('This command allows you to setup the plugin for development.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $pluginName = $this->ask('Enter the plugin name: ');
        $pluginSlug = $this->ask('Enter the plugin slug: ');
        $prefix = $this->ask('Enter the plugin prefix[space,hyphen will be converted to _]: ');
        $prefix = \str_replace([' ', '-'], ['_', '_'], $prefix);
        $functionPrefix = \lcfirst($prefix);
        $constPrefix = \strtoupper($prefix);
        $restNamespace = $this->ask('Enter the REST API namespace[space,hyphen will be converted to _]: ');
        $restNamespace = \str_replace([' ', '-'], ['_', '_'], $restNamespace);
        $rootNamespace = $this->ask('Enter the app root namespace: ');
        $appRootNamespace = \rtrim(\str_replace(' ', '', $rootNamespace), '\\');
        $replaces = ['#[plugin-name]' => $pluginName, '#[plugin-slug]' => $pluginSlug, '#[plugin-prefix]' => $prefix, '#[const-prefix]' => $constPrefix, '#[function-prefix]' => $functionPrefix, '#[rest-namespace]' => $restNamespace, '#[root-namespace]' => $rootNamespace, 'namespace App' => 'namespace ' . $appRootNamespace, 'use App' => 'use ' . $appRootNamespace, '\\App\\' => '\\' . $appRootNamespace . '\\', '"App\\\\":' => '"' . $appRootNamespace . '\\\\":'];
        \copy(__DIR__ . '/../../stubs/wpdrill.stub', WPDRILL_ROOT_PATH . '/' . $pluginSlug . '.php');
        \copy(__DIR__ . '/../../stubs/helpers.stub', WPDRILL_ROOT_PATH . '/app/Utilities/helpers.php');
        if (\file_exists(WPDRILL_ROOT_PATH . '/wpdrill.php')) {
            \rename(WPDRILL_ROOT_PATH . '/wpdrill.php', WPDRILL_ROOT_PATH . '/' . \strtolower($pluginSlug) . '.php');
        }
        $this->replaceExecute($replaces);
        $this->process(['composer', 'dump-autoload']);
        //$this->process(['composer', 'bin', 'php-scoper', 'require', '--dev', 'humbug/php-scoper']);
        $output->writeln('<info>Congratulations! Your plugin is ready to develop.</info>');
        $table = new Table($output);
        $table->setHeaderTitle("Info");
        $table->addRows([['<comment>Plugin Name</comment>', $pluginName], ['<comment>Plugin Slug</comment>', $pluginSlug], ['<comment>Plugin Prefix</comment>', $prefix], ['<comment>REST API Namespace</comment>', $restNamespace], ['<comment>Root Namespace</comment>', $rootNamespace]]);
        $table->render();
        return Command::SUCCESS;
    }
    protected function replaceExecute(array $replaces)
    {
        $directory = WPDRILL_ROOT_PATH;
        // Text to search for
        $search = \array_keys($replaces);
        // Text to replace with
        $replace = \array_values($replaces);
        $this->replace($directory, $search, $replace);
    }
    protected function replace(string $directory, array $search, array $replace)
    {
        $files = \glob($directory . '/*');
        foreach ($files as $file) {
            if (\is_file($file)) {
                $contents = \file_get_contents($file);
                $modified_contents = \str_replace($search, $replace, $contents);
                \file_put_contents($file, $modified_contents);
            }
            if (\is_dir($file)) {
                if (\basename($file) === 'vendor' || \basename($file) === 'pkgs') {
                    continue;
                }
                $this->replace($file, $search, $replace);
            }
        }
    }
}
