<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\ImportExportServices;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class ImportExportController implements InvokableContract
{
    protected ImportExportServices $importExportServices;
    /**
     *
     */
    public function __construct()
    {
        $this->importExportServices = new ImportExportServices();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
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
