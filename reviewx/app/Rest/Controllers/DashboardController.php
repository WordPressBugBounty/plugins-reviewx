<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\DashboardServices;
use Rvx\Utilities\Helper;
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
     * @param $request
     * @return Response
     */
    public function chart($request)
    {
        $resp = $this->dashboardServices->chart($request->get_params());
        return Helper::getApiResponse($resp);
    }
}
