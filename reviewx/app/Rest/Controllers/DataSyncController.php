<?php

namespace Rvx\Rest\Controllers;

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
            \date_default_timezone_set('UTC');
            $now = \microtime(\true);
            $dateTime = \DateTime::createFromFormat('U.u', \sprintf('%.6F', $now));
            $formattedDateTime = $dateTime->format('Y-m-d H:i:s.u');
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function dataSynComplete()
    {
        return Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
    }
    public function syncStatus()
    {
        $response = $this->dataSyncService->syncStatus();
        return Helper::saasResponse($response);
    }
}
