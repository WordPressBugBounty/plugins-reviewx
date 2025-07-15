<?php

namespace Rvx\ProductCategory\Handlers;

use Rvx\Api\CategoryApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
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
            \error_log("Category Not Update" . $response->getStatusCode());
            return \false;
        }
    }
}
