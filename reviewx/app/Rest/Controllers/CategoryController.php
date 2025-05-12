<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\CategoryService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class CategoryController implements InvokableContract
{
    protected $categoryService;
    /**
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
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
    public function selectable()
    {
        $resp = $this->categoryService->selectable();
        return Helper::getApiResponse($resp);
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
        return Helper::getApiResponse($resp);
    }
}
