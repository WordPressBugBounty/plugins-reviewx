<?php

namespace Rvx\Rest\Controllers;

use Throwable;
use Rvx\Models\Site;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Services\DataSyncService;
use Rvx\Utilities\Helper;
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
        try {
            $response = $this->dataSyncService->dataSync($from = 'default');
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
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
}
