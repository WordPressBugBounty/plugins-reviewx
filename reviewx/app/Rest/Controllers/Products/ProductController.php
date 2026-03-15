<?php

namespace ReviewX\Rest\Controllers\Products;

use ReviewX\Services\ProductService;
use ReviewX\Utilities\Helper;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Response;
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
        if ($resp->getStatusCode() === Response::HTTP_OK) {
            $data = $resp->getApiData();
            if (isset($data['products']) && \is_array($data['products'])) {
                foreach ($data['products'] as &$product) {
                    if (isset($product['wp_id'])) {
                        $product['post_type'] = \get_post_type($product['wp_id']);
                    }
                }
            }
            return Helper::rest($data)->success($resp()->message, $resp->getStatusCode());
        }
        return Helper::getApiResponse($resp);
    }
}
