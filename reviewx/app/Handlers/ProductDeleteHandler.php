<?php

namespace Rvx\Handlers;

use Rvx\Api\ProductApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class ProductDeleteHandler
{
    public function __invoke($product_id)
    {
        $product = get_post($product_id);
        $product = get_post($product_id);
        if ($product && $product->post_type === 'product') {
            $uid = Client::getUid() . '-' . $product_id;
            $response = (new ProductApi())->remove($uid);
            \error_log("delete data 12 " . \print_r($response, \true));
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                \error_log("Delete Status Code" . $response->getStatusCode());
                return \false;
            }
        }
    }
}
