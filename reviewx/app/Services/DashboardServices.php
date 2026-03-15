<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Api\DashboardApi;
class DashboardServices extends \ReviewX\Services\Service
{
    public function insight()
    {
        return (new DashboardApi())->insightReviews();
    }
    public function requestEmail()
    {
        return (new DashboardApi())->requestEmail();
    }
    public function requestUserData()
    {
        global $wpdb;
        $cache_key = 'rvx_dashboard_site_data';
        $site_data = \wp_cache_get($cache_key, 'reviewx');
        if (\false === $site_data) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table rvx_sites has no WP API equivalent
            $site_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}rvx_sites WHERE id = %d", 1));
            \wp_cache_set($cache_key, $site_data, 'reviewx', 3600);
            // 1 hour cache
        }
        if (!$site_data) {
            return ['success' => \false, 'message' => 'No site data found', 'data' => null];
        }
        return ['success' => \true, 'message' => 'Site user data fetched successfully', 'site_id' => $site_data->site_id, 'user_name' => $site_data->name, 'user_email' => $site_data->email, 'site_domain' => $site_data->domain, 'is_saas_synced' => $site_data->is_saas_sync];
    }
    public function chart($request)
    {
        $time = $request['view'];
        return (new DashboardApi())->chart($time);
    }
}
