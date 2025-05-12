<?php

namespace Rvx\Handlers;

use Rvx\Api\ProductApi;
use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class ProductDeleteHandler
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function __invoke($product_id)
    {
        // Define the target post types
        $enabled_post_types = $this->cptHelper->usedCPT('used');
        $post = get_post($product_id);
        $post_type = $post->post_type;
        if (!empty($enabled_post_types[$post_type]) && $enabled_post_types[$post_type] !== $post_type) {
            return;
        }
        $uid = Client::getUid() . '-' . $product_id;
        $response = (new ProductApi())->remove($uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log($post_type . " delete fails! --> " . $response->getStatusCode());
            return \false;
        }
    }
}
