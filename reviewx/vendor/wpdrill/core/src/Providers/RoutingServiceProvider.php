<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\ConfigManager;
use ReviewX\WPDrill\Routing\RouteManager;
use ReviewX\WPDrill\ServiceProvider;
class RoutingServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(RouteManager::class, function () {
            $config = $this->plugin->resolve(ConfigManager::class);
            return new RouteManager($config, $this->plugin);
        });
    }
    public function boot() : void
    {
    }
}
