<?php

namespace ReviewX\Handlers;

use ReviewX\Api\AuthApi;
use Exception;
class PluginUninstallerHandler
{
    public static function handleUninstall()
    {
        global $wpdb;
        $rvxSites = esc_sql($wpdb->prefix . 'rvx_sites');
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall read before cleanup
        $uid = $wpdb->get_var('SELECT uid FROM `' . $rvxSites . '` ORDER BY id DESC LIMIT 1');
        if ($uid) {
            try {
                // Try to inform the API about uninstall
                (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 2, 'plugin_version' => \defined('REVIEWX_VERSION') ? REVIEWX_VERSION : 'unknown', 'wp_version' => get_bloginfo('version')]);
            } catch (Exception $e) {
                // continue silently
            }
        }
        // Always clean up local data, regardless of API status
        // Clear cache on uninstall
        \wp_cache_delete('rvx_site_uid', 'reviewx');
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup
        $wpdb->query('TRUNCATE TABLE `' . $rvxSites . '`');
        (new \ReviewX\Services\CacheServices())->removeCache();
        (new \ReviewX\Services\Api\LoginService())->resetPostMeta();
        // Continue uninstall cleanly
        return \true;
    }
}
