<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Apiz\Http\Response;
use ReviewX\Api\ProductApi;
class ProductService extends \ReviewX\Services\Service
{
    /**
     * @return Response
     */
    public function getSelectProduct($data)
    {
        return (new ProductApi())->getProductSelect($data);
    }
}
