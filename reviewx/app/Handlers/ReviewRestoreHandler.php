<?php

namespace Rvx\Handlers;

class ReviewRestoreHandler
{
    public function __construct()
    {
    }
    public function __invoke($term_id)
    {
        \error_log("Term Id" . $term_id);
        //        $taxonomy = 'product_cat';
        //        if ($taxonomy === 'product_cat') {
        //            $term = get_term($term_id, $taxonomy);
        //            error_log("delete term id". $term);
        //            $category_id = $term_id;
        //            $child_id = $term->parent;
        //            $uid =  Client::getUid().'-'.$term->term_id;
        //            $response = (new CategoryApi())->remove($uid);
        //            if ($response->getStatusCode() !== Response::HTTP_OK) {
        //                error_log("Category Not Update". $response->getStatusCode());
        //                return false;
        //            }
        //        }
    }
}
