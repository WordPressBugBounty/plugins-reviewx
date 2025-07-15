<?php

namespace Rvx\Handlers;

use Rvx\Api\ProductApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class ProductUpdateHandler
{
    public function __invoke($product_id)
    {
        $product = wc_get_product($product_id);
        $payload = $this->updateData($product);
        $uid = Client::getUid() . '-' . $product_id;
        $response = (new ProductApi())->update($payload, $uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Data Not Update " . \print_r($response, \true));
            return \false;
        }
    }
    public function updateData($product)
    {
        $images = wp_get_attachment_image_src($product->image_id, 'full');
        $title = $product->get_name();
        return ['wp_id' => $product->get_id(), 'title' => isset($title) ? \htmlspecialchars($title, \ENT_QUOTES, 'UTF-8') : null, 'url' => get_permalink($product->get_id()), 'description' => isset($product->short_description) ? \htmlspecialchars($product->short_description, \ENT_QUOTES, 'UTF-8') : null, 'price' => (float) $product->regular_price, 'discounted_price' => (float) $product->price, 'slug' => $product->get_slug(), 'post_type' => get_post_type(), 'image' => $images[0] ?? null, 'status' => $this->productStatus($product->get_status()), "category_wp_unique_ids" => $this->productCategory($product)];
    }
    public function productCategory($product)
    {
        $product_categories = $product->get_category_ids();
        $parent_category_ids = array();
        foreach ($product_categories as $category_id) {
            $category = \get_term($category_id, 'product_cat');
            if ($category && $category->parent == 0) {
                $parent_category_ids[] = Client::getUid() . '-' . $category_id;
            }
        }
        return $parent_category_ids;
    }
    public function productStatus($status)
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'trash':
                return 2;
            case 'draft':
                return 3;
            default:
                return 4;
        }
    }
}
