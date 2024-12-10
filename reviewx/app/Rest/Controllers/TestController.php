<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\WcFakeDataGenerator;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class TestController
{
    public function fakeData()
    {
        (new WcFakeDataGenerator())->generate_demo_data(20, 20, 5, 20);
        return 1;
    }
    /**
     * @return Response
     */
    public function getCategory()
    {
        $resp = $this->categoryService->getCategory();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function getCategoryAll()
    {
        $resp = $this->categoryService->getCategoryAll();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function storeCategory($request)
    {
        $resp = $this->categoryService->storeCategory($request->get_params());
        // $this->wpReviewService->createReview($request);
        return Helper::getApiResponse($resp);
    }
}
