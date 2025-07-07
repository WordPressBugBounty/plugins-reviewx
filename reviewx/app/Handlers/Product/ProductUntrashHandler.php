<?php

namespace Rvx\Handlers\Product;

use Rvx\Api\ProductApi;
use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class ProductUntrashHandler
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
        $post_id = $post->ID;
        if (!isset($enabled_post_types[$post_type])) {
            return;
        }
        $uniqueId = Client::getUid() . '-' . $post_id;
        $response = (new ProductApi())->trashToRestoreWpProduct($uniqueId);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log($post_type . " restore fails! --> " . $response->getStatusCode());
            return \false;
        }
    }
}
