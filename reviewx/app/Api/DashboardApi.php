<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class DashboardApi extends \Rvx\Api\BaseApi
{
    /**
     * @return Response
     * @throws Exception
     */
    public function insightReviews() : Response
    {
        return $this->get('dashboard/insight');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function requestEmail() : Response
    {
        return $this->get('dashboard/review-request-email');
    }
    /**
     * @param $time
     * @return Response
     * @throws Exception
     */
    public function chart($time) : Response
    {
        return $this->get('dashboard/chart?view=' . $time);
    }
}
