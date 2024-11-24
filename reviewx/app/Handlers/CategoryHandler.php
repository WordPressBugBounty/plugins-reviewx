<?php

namespace Rvx\Handlers;

use Rvx\Api\CategoryApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class CategoryHandler
{
    public function __construct()
    {
    }
    public function __invoke($term_id)
    {
        $term = get_term($term_id);
        $payload = ['wp_id' => $term->term_id, 'title' => $term->name, 'slug' => $term->slug, 'taxonomy' => $term->taxonomy, 'description' => $term->description, 'parent_wp_unique_id' => Client::getUid() . '-' . $term->parent ?? null];
        $response = (new CategoryApi())->create($payload);
        \error_log("Category Response>>>" . \print_r($term, \true));
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Category Response" . $response);
            return \false;
        }
    }
}
