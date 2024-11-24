<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\ProductApi;
class ProductService extends \Rvx\Services\Service
{
    /**
     *
     */
    public function __construct()
    {
        add_action('save_post', [$this, 'saveProduct'], 10, 1);
    }
    /**
     * @return Response
     */
    public function getSelectProduct($data)
    {
        return (new ProductApi())->getProductSelect($data);
    }
    /**
     * @param $product_id
     * @return void
     */
    public function saveProduct($product_id)
    {
        $WC_Product = wc_get_product($product_id);
        \error_log("My Products" . $WC_Product);
    }
}
