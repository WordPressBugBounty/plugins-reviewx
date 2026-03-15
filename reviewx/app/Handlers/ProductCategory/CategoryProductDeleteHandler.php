<?php

namespace ReviewX\ProductCategory\Handlers;

use ReviewX\Api\CategoryApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Response;
class CategoryProductDeleteHandler
{
    public function __invoke($term_id)
    {
        $term = \get_term($term_id);
        $category_id = $term_id;
        $child_id = $term->parent;
        $uid = Client::getUid() . '-' . $term->term_id;
        $response = (new CategoryApi())->remove($uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
    }
}
