<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\ProductApi;
class ProductService extends \Rvx\Services\Service
{
    /**
     * @return Response
     */
    public function getSelectProduct($data)
    {
        return (new ProductApi())->getProductSelect($data);
    }
}
