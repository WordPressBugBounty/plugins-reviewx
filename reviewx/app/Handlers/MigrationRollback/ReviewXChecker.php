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
        // Use get_option which is the standard WP way and handles caching
        $option_value = \get_option($option_name);
        return !empty($option_value);
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
        $option_name_latest = '_rvx_settings_product';
        // Use get_option which handles caching and is compliant
        $option_exists = \get_option($option_name);
        if (empty($option_exists)) {
            $option_exists = \get_option($option_name_latest);
        }
        return !empty($option_exists);
    }
}
