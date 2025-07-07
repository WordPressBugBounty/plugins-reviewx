<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
class PluginUninstallerHandler
{
    public static function handleUninstall()
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 2, 'plugin_version' => $plugin_version ?? RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
        }
        $wpdb->query("TRUNCATE TABLE {$rvxSites}");
    }
}
