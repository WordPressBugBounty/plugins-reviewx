<?php

namespace Rvx\Services;

\defined("ABSPATH") || exit;
use Rvx\Api\DashboardApi;
class DashboardServices extends \Rvx\Services\Service
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
        $site_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}rvx_sites WHERE id = %d", 1));
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
