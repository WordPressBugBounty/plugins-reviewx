<?php

namespace ReviewX\Handlers;

use ReviewX\Handlers\MigrationRollback\SharedMethods;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class OnboardMenuHandler implements InvokableContract
{
    public function __invoke()
    {
        global $wpdb;
        $rvxSites = \esc_sql($wpdb->prefix . 'rvx_sites');
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time cleanup action
        $wpdb->query('TRUNCATE TABLE `' . $rvxSites . '`');
        $sharedMethods = new SharedMethods();
        $is_pro_active = $sharedMethods->reviewx_is_old_pro_plugin_active();
        if ($is_pro_active === \true) {
            // Old Pro version is detected, let's deactivate
            $sharedMethods->reviewx_deactivate_old_pro_plugin();
            // Reload the page once to remove Old Pro plugin's notices
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple flag check, no form data processing
            if (!isset($_GET['rvx_reloaded'])) {
                $current_uri = isset($_SERVER['REQUEST_URI']) ? \esc_url_raw(\wp_unslash($_SERVER['REQUEST_URI'])) : '';
                $url = \add_query_arg('rvx_reloaded', '1', $current_uri);
                \header('Location: ' . $url);
                exit;
            }
        }
        View::output('storeadmin/onboard', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
