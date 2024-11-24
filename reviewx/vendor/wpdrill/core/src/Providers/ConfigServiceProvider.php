<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\ConfigManager;
use Rvx\WPDrill\ServiceProvider;
class ConfigServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ConfigManager::class, function () {
            return new \Rvx\WPDrill\ConfigManager($this->plugin->getPath('config'));
        });
    }
    public function boot() : void
    {
    }
}
