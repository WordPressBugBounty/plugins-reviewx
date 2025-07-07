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
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 0, 'plugin_version' => $plugin_version ?? RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
        }
    }
}
