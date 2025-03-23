<?php

namespace Rvx\Rest\Controllers;

use Throwable;
use Rvx\Models\Site;
use Rvx\WPDrill\Response;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Services\DataSyncService;
use Rvx\Utilities\Helper;
use Rvx\Rest\Controllers\CptController;
use Rvx\CPT\CptHelper;
use Rvx\Services\SettingService;
use Rvx\WPDrill\Facades\Request;
class DataSyncController
{
    protected SettingService $settingService;
    protected $dataSyncService;
    public function __construct()
    {
        $this->dataSyncService = new DataSyncService();
        $this->settingService = new SettingService();
    }
    public function dataSync()
    {
        $resp = $this->dataSyncService->dataSync($from = 'default');
        if ($resp) {
            return Helper::rvxApi()->success('Data Synced Successfully');
        } else {
            return Helper::rvxApi()->fails('Data Sync Failed');
        }
    }
    public function dataSynComplete()
    {
        // Update all DB settings from API to WP DB
        $this->updateSettingsOnSync();
        return Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
    }
    public function syncStatus()
    {
        \header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        \header("Pragma: no-cache");
        $response = $this->dataSyncService->syncStatus();
        if ($response->getApiData()['sync_stats'] === 1) {
            // Update all DB settings from API to WP DB
            $this->updateSettingsOnSync();
            Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
        }
        return Helper::saasResponse($response);
    }
    public function updateSettingsOnSync()
    {
        // Save '_rvx_cpt_settings' data after sync is completed from Sass API to WP DB
        $response = (new CptController())->cptGetOnSync();
        if ($response[0] === \true) {
            // Get the enabled post types array
            $used_post_types = (new CptHelper())->usedCPTOnSync('used');
            // Loop through each post type and call getApiReviewSettings
            foreach ($used_post_types as $post_type) {
                $review_response = (new \Rvx\Rest\Controllers\SettingController())->getApiReviewSettingsOnSync($post_type);
                // Update Review settings
                $review_settings = $review_response['data']['review_settings'];
                $this->settingService->updateReviewSettingsOnSync($review_settings, \strtolower($post_type));
            }
        }
        // Get widget settings
        $widget_response = (new \Rvx\Rest\Controllers\SettingController())->getApiWidgetSettingsOnSync();
        $widget_settings = $widget_response['data']['widget_settings'];
        // Update widget settings
        $this->settingService->updateWidgetSettings($widget_settings);
    }
    public function dataManualSync($request)
    {
        try {
            $response = $this->dataSyncService->dataManualSync($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function syncedData(\WP_REST_Request $request)
    {
        $file_path = WP_CONTENT_DIR . '/uploads/reviewx/shop-bulk-data.jsonl';
        if (!\file_exists($file_path)) {
            return Helper::rvxApi()->fails('File not found', 404);
        }
        \header('Content-Description: File Transfer');
        \header('Content-Type: application/jsonl');
        \header('Content-Disposition: attachment; filename="' . \basename($file_path) . '"');
        \header('Expires: 0');
        \header('Cache-Control: must-revalidate');
        \header('Pragma: public');
        \header('Content-Length: ' . \filesize($file_path));
        \readfile($file_path);
        exit;
    }
}
