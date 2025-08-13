<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Rvx\WPDrill\Contracts\InvokableContract;
class PluginDeactivatedHandler implements InvokableContract
{
    public function __invoke()
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            // Change rvx_sites table is_saas_sync to 0
            $wpdb->update(
                $rvxSites,
                ['is_saas_sync' => 0],
                ['uid' => $uid],
                ['%d'],
                // format for is_saas_sync (integer)
                ['%s']
            );
            // Set the localStorage isAlreadySyncSuccess to false
            update_option('rvx_reset_sync_flag', \true);
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 0, 'plugin_version' => $plugin_version ?? RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
        }
    }
}
