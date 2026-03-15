<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\ConfigManager;
use ReviewX\WPDrill\ServiceProvider;
class ConfigServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ConfigManager::class, function () {
            return new \ReviewX\WPDrill\ConfigManager($this->plugin->getPath('config'));
        });
    }
    public function boot() : void
    {
    }
}
