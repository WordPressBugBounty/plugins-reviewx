<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\DB\Migration\Migrator;
use ReviewX\WPDrill\Routing\RouteManager;
use ReviewX\WPDrill\ServiceProvider;
class MigrationServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(Migrator::class, function () {
            return new Migrator($this->plugin->getPath('database/migrations'));
        });
    }
    public function boot() : void
    {
    }
}
