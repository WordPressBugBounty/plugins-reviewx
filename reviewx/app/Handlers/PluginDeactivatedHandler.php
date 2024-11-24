<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\Migration\Migrator;
class PluginDeactivatedHandler implements InvokableContract
{
    public function __invoke()
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $wpdb->query("TRUNCATE TABLE {$rvxSites}");
    }
}
