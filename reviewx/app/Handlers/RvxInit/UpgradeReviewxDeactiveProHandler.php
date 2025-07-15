<?php

namespace Rvx\Handlers\RvxInit;

use Rvx\Api\AuthApi;
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
                    deactivate_plugins($reviewxProDeactive);
                    // Deactivate the plugin
                }
            }
            $response = wp_remote_get("https://api.wordpress.org/plugins/info/1.0/reviewx.json");
            if (!\is_wp_error($response)) {
                $plugin_data = \json_decode(wp_remote_retrieve_body($response));
                if ($plugin_data && isset($plugin_data->version)) {
                    global $wpdb;
                    $rvxSites = $wpdb->prefix . 'rvx_sites';
                    $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
                    $plugin_version = $plugin_data->version;
                    if ($uid) {
                        (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => $plugin_version ?? RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
                    }
                }
            }
        }
    }
}
