<?php

namespace Rvx\Rest\Controllers;

use Throwable;
use Rvx\Models\Site;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Services\DataSyncService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\Request;
class DataSyncController implements InvokableContract
{
    protected $dataSyncService;
    /**
     * @param DataSyncService $dataSyncServices
     */
    public function __construct(DataSyncService $dataSyncService)
    {
        $this->dataSyncService = $dataSyncService;
    }
    /**
     * @return void
     */
    public function __invoke()
    {
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
        return Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
    }
    public function syncStatus()
    {
        \header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        \header("Pragma: no-cache");
        $response = $this->dataSyncService->syncStatus();
        if ($response->getApiData()['sync_stats'] === 1) {
            Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
        }
        return Helper::saasResponse($response);
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
