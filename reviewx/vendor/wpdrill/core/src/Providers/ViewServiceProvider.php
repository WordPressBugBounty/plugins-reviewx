<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\ServiceProvider;
use Rvx\WPDrill\Views\ViewManager;
class ViewServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ViewManager::class, function () {
            return new \Rvx\WPDrill\Views\ViewManager($this->plugin);
        });
    }
    public function boot() : void
    {
    }
}
