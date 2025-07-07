<?php

namespace Rvx\Rest\Controllers;

use Throwable;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
use Rvx\Services\CacheServices;
use Rvx\Services\ImportExportServices;
use Rvx\WPDrill\Contracts\InvokableContract;
class ImportExportController implements InvokableContract
{
    protected ImportExportServices $importExportServices;
    protected CacheServices $cacheServices;
    /**
     *
     */
    public function __construct()
    {
        $this->importExportServices = new ImportExportServices();
        $this->cacheServices = new CacheServices();
    }
    public function __invoke()
    {
        // This method is required by the InvokableContract but not used in this controller.
    }
    /**
     * @param $request
     * @return Response
     */
    public function importSupportedAppStore($request)
    {
        try {
            $response = $this->importExportServices->importSupportedAppStore($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     * @throws Throwable
     */
    public function importStore($request)
    {
        try {
            $response = $this->importExportServices->importStore($request);
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('FIle Not Import', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function exportCsv($request)
    {
        try {
            $response = $this->importExportServices->exportCsv($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function exportHistory()
    {
        $resp = $this->importExportServices->exportHistory();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function importHistory()
    {
        $resp = $this->importExportServices->importHistory();
        return Helper::getApiResponse($resp);
    }
    public function importRollback($request)
    {
        try {
            $response = $this->importExportServices->importRollback($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
    public function importRestore($request)
    {
        try {
            $response = $this->importExportServices->importRestore($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
}
