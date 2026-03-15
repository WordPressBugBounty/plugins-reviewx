<?php

namespace ReviewX\Handlers;

use ReviewX\Api\ProductApi;
use ReviewX\CPT\CptHelper;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Response;
use ReviewX\Services\CacheServices;
class ProductDeleteHandler
{
    protected $cptHelper;
    protected $cacheServices;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($product_id)
    {
        // Define the target post types
        $enabled_post_types = $this->cptHelper->usedCPT('used');
        $post = \get_post($product_id);
        $post_type = $post->post_type;
        if (!isset($enabled_post_types[$post_type])) {
            return;
        }
        $uid = Client::getUid() . '-' . $product_id;
        $response = (new ProductApi())->remove($uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
        $this->cacheServices->removeCache();
    }
}
