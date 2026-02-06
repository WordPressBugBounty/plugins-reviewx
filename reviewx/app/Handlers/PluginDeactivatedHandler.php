<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Rvx\WPDrill\Contracts\InvokableContract;
use Exception;
class PluginDeactivatedHandler implements InvokableContract
{
    public function __invoke() : void
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            // Change rvx_sites table is_saas_sync to 0
            $wpdb->update($rvxSites, ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
            // Mark sync flag locally
            set_transient('rvx_reset_sync_flag', \true);
            try {
                // Attempt API call — skip gracefully on failure
                (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 0, 'plugin_version' => \defined('RVX_VERSION') ? RVX_VERSION : 'unknown', 'wp_version' => get_bloginfo('version')]);
            } catch (Exception $e) {
                // Log quietly instead of breaking plugin disable process
                if (\defined('WP_DEBUG') || WP_DEBUG) {
                    \error_log('[ReviewX] PluginDeactivatedHandler: API call failed - ' . $e->getMessage());
                }
                // continue silently
            }
        }
        // Always clean up local data, regardless of API status
        (new \Rvx\Services\CacheServices())->removeCache();
        (new \Rvx\Services\Api\LoginService())->resetPostMeta();
        // Continue plugin deactivation
        return;
    }
}
