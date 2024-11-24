<?php

namespace Rvx\Migration;

class ReviewXChecker
{
    // Check if ReviewX (free) plugin is active, installed, and data exists
    public static function isReviewXActive()
    {
        global $wpdb;
        // Get active plugins from the options table
        $active_plugins = get_option('active_plugins', []);
        // Look for any plugin path that contains 'reviewx' (for the free version)
        $is_reviewx_installed = \false;
        foreach ($active_plugins as $plugin) {
            if (\strpos($plugin, 'reviewx') !== \false) {
                $is_reviewx_installed = \true;
                break;
            }
        }
        if (!$is_reviewx_installed) {
            return \false;
            // Plugin is not active
        }
        // Define table name for ReviewX free version
        $table_name = $wpdb->prefix . 'reviewx';
        // Check if table exists and has data
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        $data_exists = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}") > 0;
        return $table_exists && $data_exists;
    }
    // Check if ReviewX Pro plugin is active, installed, and data exists
    public static function isReviewXProActive()
    {
        global $wpdb;
        // Get active plugins from the options table
        $active_plugins = get_option('active_plugins', []);
        // Look for any plugin path that contains 'reviewx-pro' for the Pro version
        $is_reviewx_pro_installed = \false;
        foreach ($active_plugins as $plugin) {
            if (\strpos($plugin, 'reviewx-pro') !== \false) {
                $is_reviewx_pro_installed = \true;
                break;
            }
        }
        if (!$is_reviewx_pro_installed) {
            return \false;
            // Plugin is not active
        }
        // Define table name for ReviewX Pro
        $pro_table_name = $wpdb->prefix . 'reviewx_pro';
        // Check if Pro table exists and has data
        $pro_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$pro_table_name}'") === $pro_table_name;
        $pro_data_exists = $wpdb->get_var("SELECT COUNT(*) FROM {$pro_table_name}") > 0;
        return $pro_table_exists && $pro_data_exists;
    }
}
// Usage
//$is_reviewx_active = ReviewXChecker::isReviewXActive();
//$is_reviewx_pro_active = ReviewXChecker::isReviewXProActive();
