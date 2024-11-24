<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\ConfigManager;
use Rvx\WPDrill\Routing\RouteManager;
use Rvx\WPDrill\ServiceProvider;
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
