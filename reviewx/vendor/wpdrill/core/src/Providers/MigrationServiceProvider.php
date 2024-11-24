<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\DB\Migration\Migrator;
use Rvx\WPDrill\Routing\RouteManager;
use Rvx\WPDrill\ServiceProvider;
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
