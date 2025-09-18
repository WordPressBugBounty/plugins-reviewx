<?php

namespace Rvx\Rest\Controllers;

use Throwable;
use Rvx\Services\GoogleReviewService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class GoogleReviewController implements InvokableContract
{
    protected $googleReviewService;
    /**
     * @param GoogleReviewService $googleReviewService
     */
    public function __construct(GoogleReviewService $googleReviewService)
    {
        $this->googleReviewService = $googleReviewService;
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function googleReviewGet()
    {
        $resp = $this->googleReviewService->googleReviewGet();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function googleReviewPlaceApi()
    {
        $resp = $this->googleReviewService->googleReviewPlaceApi();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function googleRecaptchaVerify($request)
    {
        $resp = $this->googleReviewService->googleRecaptchaVerify($request->get_params());
        return $resp;
    }
    /**
     * @return Response
     */
    public function googleReviewKey($request)
    {
        try {
            $response = $this->googleReviewService->googleReviewKey($request->get_params());
            \delete_transient('rvx_google_api_settings');
            // When settings is changed remove the cache wpdb transient data
            \delete_transient('rvx_google_reviews_cache');
            // When settings is changed remove the cache wpdb transient data
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function googleReviewSetting($request)
    {
        try {
            $response = $this->googleReviewService->googleReviewSetting($request->get_params());
            \delete_transient('rvx_google_api_settings');
            // When settings is changed remove the cache wpdb transient data
            \delete_transient('rvx_google_reviews_cache');
            // When settings is changed remove the cache wpdb transient data
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
}
