<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
use ReviewX\WPDrill\ServiceProvider;
class DBServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(QueryBuilderHandler::class, function () {
            global $wpdb;
            $connection = new \ReviewX\WPDrill\DB\Connection($wpdb, ['prefix' => $wpdb->prefix]);
            return new QueryBuilderHandler($connection);
        });
    }
    public function boot() : void
    {
    }
}
