<?php

namespace Rvx\Rest\Controllers\Products;

use Rvx\Services\ProductService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class ProductController implements InvokableContract
{
    protected $productService;
    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function selectable($request)
    {
        $resp = $this->productService->getSelectProduct($request->get_params());
        return Helper::getApiResponse($resp);
    }
}
