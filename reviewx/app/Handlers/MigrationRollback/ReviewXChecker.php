<?php

namespace Rvx\Handlers\MigrationRollback;

class ReviewXChecker
{
    /**
     * Check if ReviewX v1 (free) plugin database data exists.
     *
     * @return bool
     */
    public static function isReviewXExists() : bool
    {
        global $wpdb;
        // Define option name for ReviewX free version
        $option_name = '_rx_option_review_criteria';
        // Check if the option exists in the wp_options table
        $option_exists = $wpdb->get_var($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option_name));
        return !empty($option_exists);
    }
    /**
     * Check if ReviewX v2 SaaS plugin database data exists.
     *
     * @return bool
     */
    public static function isReviewXSaasExists() : bool
    {
        global $wpdb;
        // Define option name for ReviewX SaaS version
        $option_name = '_rvx_settings_data';
        // Check if the option exists in the wp_options table
        $option_exists = $wpdb->get_var($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option_name));
        return !empty($option_exists);
    }
}
