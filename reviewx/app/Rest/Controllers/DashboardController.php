<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\DashboardServices;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class DashboardController implements InvokableContract
{
    protected $dashboardServices;
    /**
     *
     */
    public function __construct(DashboardServices $dashboardServices)
    {
        $this->dashboardServices = $dashboardServices;
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
    public function insight()
    {
        $resp = $this->dashboardServices->insight();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function requestEmail()
    {
        $resp = $this->dashboardServices->requestEmail();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return \WPDrill\Response
     */
    public function requestUserData()
    {
        try {
            $response = $this->dashboardServices->requestUserData();
            return Helper::rvxApi($response)->success('Site data Retrieved Successfully');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Site data Retrieval Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function chart($request)
    {
        $resp = $this->dashboardServices->chart($request->get_params());
        return Helper::getApiResponse($resp);
    }
}
