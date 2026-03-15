<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\ServiceProvider;
use ReviewX\WPDrill\Views\ViewManager;
class ViewServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ViewManager::class, function () {
            return new \ReviewX\WPDrill\Views\ViewManager($this->plugin);
        });
    }
    public function boot() : void
    {
    }
}
