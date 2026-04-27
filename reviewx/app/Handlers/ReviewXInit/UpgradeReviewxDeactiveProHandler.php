<?php

namespace ReviewX\Handlers\ReviewXInit;

use ReviewX\Api\AuthApi;
class UpgradeReviewxDeactiveProHandler
{
    public function __invoke($upgrader_object, $options)
    {
        if ($options['type'] === 'plugin' && isset($options['plugins'])) {
            // Your plugin's main file
            $reviewxFilePath = plugin_basename(__FILE__);
            // Check if your plugin is being updated
            if (\in_array($reviewxFilePath, $options['plugins'], \true)) {
                $reviewxProDeactive = 'reviewx-pro/reviewx-pro.php';
                // Path to the plugin to deactivate
                // Check if the target plugin is active
                if (is_plugin_active($reviewxProDeactive)) {
                    \deactivate_plugins($reviewxProDeactive);
                    // Deactivate the plugin
                }
            }
            $response = wp_remote_get("https://api.wordpress.org/plugins/info/1.0/reviewx.json");
            if (!\is_wp_error($response)) {
                $plugin_data = \json_decode(wp_remote_retrieve_body($response));
                if ($plugin_data && isset($plugin_data->version)) {
                    global $wpdb;
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name from $wpdb->prefix, safe
                    $uid = $wpdb->get_var("SELECT uid FROM {$wpdb->prefix}rvx_sites ORDER BY id DESC LIMIT 1");
                    $plugin_version = $plugin_data->version;
                    if ($uid) {
                        (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => $plugin_version ?? REVIEWX_VERSION, 'wp_version' => \get_bloginfo('version')]);
                    }
                }
            }
        }
    }
}
