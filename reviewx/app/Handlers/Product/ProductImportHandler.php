<?php

namespace Rvx\Handlers\Product;

use Rvx\Api\ProductApi;
use Rvx\WPDrill\Response;
class ProductImportHandler
{
    public function __invoke($product, $data)
    {
        $payload = $this->prepareData($product);
        $response = (new ProductApi())->create($payload);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Imported product not import" . $response->getStatusCode());
            return \false;
        }
    }
    public function prepareData($product)
    {
        $product_id = $product->get_id();
        $images = wp_get_attachment_image_src($product_id, 'full');
        return [
            "wp_id" => $product->get_id(),
            "title" => $product->get_name(),
            "url" => get_permalink($product->get_id()),
            "description" => $product->get_short_description(),
            "price" => isset($_POST['_regular_price']) ? (float) sanitize_text_field($_POST['_regular_price']) : (float) $product->get_regular_price(),
            "discounted_price" => (float) sanitize_text_field($product->get_sale_price()) ?? 0,
            "slug" => $product->get_slug(),
            "image" => $images ? $images[0] : '',
            "status" => $this->productStatus($product->get_status()),
            "post_type" => 'product',
            // Hardcoded due to import issue
            "total_reviews" => (int) $product->get_review_count(),
            "avg_rating" => (float) $product->get_average_rating(),
            "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0],
            "one_stars" => 0,
            "two_stars" => 0,
            "three_stars" => 0,
            "four_stars" => 0,
            "five_stars" => 0,
            "category_wp_unique_ids" => $this->productCategory($product),
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
        $data = ["wp_id" => $post->ID, "title" => $post->post_title, "url" => get_permalink($post->ID), "description" => $post->post_excerpt, "price" => 0, "discounted_price" => 0, "slug" => $post->post_name, "image" => '', "status" => $this->productStatus($post->post_status), "post_type" => get_post_type($post->ID), "total_reviews" => (int) get_comments_number($post->ID) ?? 0, "avg_rating" => 0.0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => [\Rvx\Utilities\Auth\Client::getUid() . '-' . 0]];
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
