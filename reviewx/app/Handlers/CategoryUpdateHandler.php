<?php

namespace Rvx\Handlers;

use Rvx\Api\CategoryApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class CategoryUpdateHandler
{
    public function __construct()
    {
    }
    public function __invoke($term_id)
    {
        $term = get_term($term_id);
        // $category_id = $term_id;
        // $child_id = $term->parent;
        $uid = Client::getUid() . '-' . $term->term_id;
        $payload = [
            //                'wp_id' => $term->term_id,
            'title' => $term->name,
            //                'slug' => $term->slug,
            'description' => $term->description,
            'taxonomy' => $term->taxonomy,
        ];
        \error_log("Category update " . \print_r($payload, \true));
        $response = (new CategoryApi())->update($payload, $uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Category Not Update" . $response->getStatusCode());
            return \false;
        }
    }
}
