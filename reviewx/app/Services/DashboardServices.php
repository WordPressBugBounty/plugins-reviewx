<?php

namespace Rvx\Services;

use Rvx\Api\DashboardApi;
class DashboardServices extends \Rvx\Services\Service
{
    public function insight()
    {
        return (new DashboardApi())->insightReviews();
    }
    public function requestEmail()
    {
        return (new DashboardApi())->requestEmail();
    }
    public function chart($request)
    {
        $time = $request['view'];
        return (new DashboardApi())->chart($time);
    }
}
