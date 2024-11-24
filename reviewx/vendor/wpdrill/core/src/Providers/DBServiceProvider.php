<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
use Rvx\WPDrill\ServiceProvider;
class DBServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(QueryBuilderHandler::class, function () {
            global $wpdb;
            $connection = new \Rvx\WPDrill\DB\Connection($wpdb, ['prefix' => $wpdb->prefix]);
            return new QueryBuilderHandler($connection);
        });
    }
    public function boot() : void
    {
    }
}
