<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Exception;
class PluginUninstallerHandler
{
    public static function handleUninstall()
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            try {
                // Try to inform the API about uninstall
                (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 2, 'plugin_version' => \defined('RVX_VERSION') ? RVX_VERSION : 'unknown', 'wp_version' => get_bloginfo('version')]);
            } catch (Exception $e) {
                // If API fails, do not interrupt uninstall
                if (\defined('WP_DEBUG') && WP_DEBUG) {
                    \error_log('[ReviewX] PluginUninstallerHandler: API call failed - ' . $e->getMessage());
                }
                // continue silently
            }
        }
        // Always clean up local data, regardless of API status
        $wpdb->query("TRUNCATE TABLE {$rvxSites}");
        (new \Rvx\Services\CacheServices())->removeCache();
        (new \Rvx\Services\Api\LoginService())->resetPostMeta();
        // Continue uninstall cleanly
        return \true;
    }
}
