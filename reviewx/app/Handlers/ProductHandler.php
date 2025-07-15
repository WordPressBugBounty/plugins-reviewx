<?php

namespace Rvx\Handlers;

use Rvx\Api\ProductApi;
use Rvx\WPDrill\Response;
class ProductHandler
{
    public function __invoke($new_status, $old_status, $product)
    {
        if ($new_status == 'publish' && $old_status != 'publish') {
            $post = get_post($product->ID);
            if ($post->post_type === 'product') {
                switch ($new_status) {
                    case 'publish':
                        $currentProduct = wc_get_product($product->ID);
                        $payload = $this->prepareData($currentProduct);
                        $response = (new ProductApi())->create($payload);
                        if ($response->getStatusCode() !== Response::HTTP_OK) {
                            \error_log("Product Not insert" . $response->getStatusCode());
                            return \false;
                        }
                        break;
                }
            } else {
                $payload = $this->customPost($post);
                \error_log("Custom post" . \print_r($payload, \true));
                $response = (new ProductApi())->create($payload);
                if ($response->getStatusCode() !== Response::HTTP_OK) {
                    \error_log("Cpt Not insert" . $response);
                    return \false;
                }
            }
        }
    }
    public function prepareData($currentProduct)
    {
        $images = wp_get_attachment_image_src($currentProduct->image_id, 'full');
        $title = $currentProduct->get_name();
        return [
            "wp_id" => $currentProduct->get_id(),
            "title" => isset($title) ? \htmlspecialchars($title, \ENT_QUOTES, 'UTF-8') : null,
            "url" => get_permalink($currentProduct->get_id()),
            "description" => isset($currentProduct->short_description) ? \htmlspecialchars($currentProduct->short_description, \ENT_QUOTES, 'UTF-8') : null,
            "price" => isset($_POST['_regular_price']) ? (float) sanitize_text_field($_POST['_regular_price']) : 0,
            "discounted_price" => isset($_POST['_sale_price']) ? (float) sanitize_text_field($_POST['_sale_price']) : 0,
            "slug" => $currentProduct->get_slug(),
            "image" => $images[0] ?? '',
            "status" => $this->productStatus($currentProduct->get_status()),
            "post_type" => 'product',
            //get_post_type() code not use because import product issue
            "total_reviews" => (int) $currentProduct->get_review_count(),
            "avg_rating" => (float) $currentProduct->get_average_rating(),
            "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0],
            "one_stars" => 0,
            "two_stars" => 0,
            "three_stars" => 0,
            "four_stars" => 0,
            "five_stars" => 0,
            "category_wp_unique_ids" => $this->productCategory($currentProduct),
        ];
    }
    public function productStatus($status)
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'trash':
                return 2;
            default:
                return 3;
        }
    }
    public function productCategory($product)
    {
        $product_categories = $product->get_category_ids();
        $parent_category_ids = array();
        foreach ($product_categories as $category_id) {
            $category = \get_term($category_id, 'product_cat');
            if ($category && $category->parent == 0) {
                $parent_category_ids[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . $category_id;
            }
        }
        if (empty($parent_category_ids)) {
            $parent_category_ids[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . '1';
            return $parent_category_ids;
        }
        return $parent_category_ids;
    }
    public function customPost($post)
    {
        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        $data = ["wp_id" => $post->ID, "title" => isset($post->post_title) ? \htmlspecialchars($post->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => get_permalink($post->ID), "description" => isset($post->post_excerpt) ? \htmlspecialchars($post->post_excerpt, \ENT_QUOTES, 'UTF-8') : null, "price" => 0, "discounted_price" => 0, "slug" => $post->post_name, "image" => $image_url ?? '', "status" => $this->productStatus($post->post_status), "post_type" => get_post_type($post->ID), "total_reviews" => (int) get_comments_number($post->ID) ?? 0, "avg_rating" => 0.0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => [\Rvx\Utilities\Auth\Client::getUid() . '-' . 0]];
        return $data;
    }
    public function getPostCategoryIds($post_ids)
    {
        if (empty($post_ids)) {
            \error_log("No valid post ID provided.");
            return [];
        }
        if (empty($category_ids)) {
            \error_log("No categories found for post ID: " . $post_ids);
            return [];
        }
        $parent_category_ids = [];
        foreach ($category_ids as $category_id) {
            $parent_category_ids[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . $category_id;
        }
        if ($parent_category_ids) {
            return $parent_category_ids;
        }
        return $parent_category_ids[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . 0;
    }
}
