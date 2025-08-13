<?php

namespace Rvx\Rest\Controllers;

use Rvx\Import\Judgeme\JudgemeReviewsImport;
use Throwable;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
use Rvx\Services\CacheServices;
use Rvx\WPDrill\Contracts\InvokableContract;
class ImportJudgeMeController implements InvokableContract
{
    protected CacheServices $cacheServices;
    protected JudgemeReviewsImport $judgemeReviewsImport;
    /**
     *
     */
    public function __construct()
    {
        $this->judgemeReviewsImport = new JudgemeReviewsImport();
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
    public function judgemeStatusDetect($request)
    {
        try {
            $response = $this->judgemeReviewsImport->judgemeStatusDetect($request->get_params());
            return Helper::rvxApi($response)->success('Judgeme status detect sucessfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme status detection Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function judgemeCSVdownload($request)
    {
        try {
            $response = $this->judgemeReviewsImport->judgemeCSVdownload($request->get_params());
            return Helper::rvxApi($response)->success('Judgeme CSV Downloaded Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme CSV Download Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function judgemeCSVUpload($request)
    {
        try {
            $response = $this->judgemeReviewsImport->judgemeCSVUpload($request->get_params());
            return Helper::rvxApi($response)->success('Judgeme CSV Uploaded Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme CSV Upload Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function judgemeImportChunk($request)
    {
        try {
            $response = $this->judgemeReviewsImport->judgemeImportChunk($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::rvxApi($response)->success('Judgeme Chunk Imported Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme Chunk Import Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     * @throws Throwable
     */
    public function judgemeImportStatus($request)
    {
        try {
            $response = $this->judgemeReviewsImport->judgemeImportStatus($request);
            return Helper::rvxApi($response)->success('Judgeme Import Status Retrieved Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme Import Status Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     * @throws Throwable
     */
    public function judgemeDataSaasSync($request)
    {
        try {
            $response = $this->judgemeReviewsImport->rvxReviewsSync($request);
            return Helper::rvxApi($response)->success('Judgeme Reviews Sync Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Judgeme Reviews Sync Failed', $e->getCode());
        }
    }
}
