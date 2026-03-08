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
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Table name from $wpdb->prefix, safe
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            try {
                // Try to inform the API about uninstall
                (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 2, 'plugin_version' => \defined('RVX_VERSION') ? RVX_VERSION : 'unknown', 'wp_version' => get_bloginfo('version')]);
            } catch (Exception $e) {
                // continue silently
            }
        }
        // Always clean up local data, regardless of API status
        // Clear cache on uninstall
        \wp_cache_delete('rvx_site_uid', 'reviewx');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from $wpdb->prefix, safe
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}rvx_sites");
        (new \Rvx\Services\CacheServices())->removeCache();
        (new \Rvx\Services\Api\LoginService())->resetPostMeta();
        // Continue uninstall cleanly
        return \true;
    }
}
