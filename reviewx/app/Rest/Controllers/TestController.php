<?php

namespace Rvx\Rest\Controllers;

use Rvx\Models\Post;
use Rvx\Api\ProductApi;
use Rvx\Models\Category;
use Rvx\Handlers\CustomOrderUpdate;
use Rvx\Services\Api\LoginService;
use Rvx\Services\DataSyncService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
class TestController
{
    public function test($request)
    {
        /*$arr = [
                    "description" => 'После вебинара я практиковала с Узником Смерти. Давала ему задание на разрушение ситуации. У меня в один день было 2-е ситуации, одну из которых мне нужно было разрушить - перенести на 1 день. Я, конечно, хотела, что бы разрушилась (перенеслась на 1 день) первая ситуация, но она была связана с энергиями 13 аркана. Произошло всё наилучшим образом - неожиданно перенеслась 2-я ситуация - "сбой по техническим причинам". Моей радости не было предела. Место, где я прикрепила Узника Смерти обнесли забором, охраняют))). Благодарю.',
                    "wp_unique_id" => 'si-U4BO-vTo6aSHW-LBR7-1331'
        
                ];
        
                update_post_meta(
                    1200,
                    "_json",
                    $arr
                );*/
        $jsonArr = get_post_meta(1200, '_json');
        dd(\json_encode(maybe_unserialize("kasldfjklasjfkl"), \JSON_UNESCAPED_UNICODE));
    }
    public function developer($request)
    {
        $ls = new LoginService();
        $ls->resetPostMeta();
        echo 'Post meta Cleared';
    }
    public function prepareData($currentProduct)
    {
        $images = wp_get_attachment_image_src($currentProduct->image_id, 'full');
        return ["wp_id" => $currentProduct->get_id(), "title" => $currentProduct->get_name(), "url" => get_permalink($currentProduct->get_id()), "description" => $currentProduct->short_description, "price" => (float) sanitize_text_field($_POST['_regular_price']), "discounted_price" => (float) sanitize_text_field($_POST['_sale_price']), "slug" => $currentProduct->get_slug(), "image" => $images[0] ?? '', "status" => $this->productStatus($currentProduct->get_status()), "post_type" => get_post_type() ?? 'product', "total_reviews" => (int) $currentProduct->get_review_count(), "avg_rating" => $currentProduct->get_average_rating(), "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->productCategory($currentProduct)];
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
            $category = get_term($category_id, 'product_cat');
            if ($category && $category->parent == 0) {
                $parent_category_ids[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . $category_id;
            }
        }
        return $parent_category_ids;
    }
    public function customPost($post)
    {
        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        $data = [
            "wp_id" => $post->ID,
            "title" => $post->post_title,
            "url" => get_permalink($post->ID),
            "description" => $post->post_excerpt,
            "price" => 0,
            "discounted_price" => 0,
            "slug" => $post->post_name,
            "image" => '',
            "status" => $this->productStatus($post->post_status),
            "post_type" => get_post_type($post->ID),
            "total_reviews" => (int) get_comments_number($post->ID) ?? 0,
            "avg_rating" => 0,
            "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0],
            "one_stars" => 0,
            "two_stars" => 0,
            "three_stars" => 0,
            "four_stars" => 0,
            "five_stars" => 0,
            // "category_wp_unique_ids" => $this->getPostCategoryIds($post->ID)
            "category_wp_unique_ids" => [\Rvx\Utilities\Auth\Client::getUid() . '-' . 0],
        ];
        // error_log("Data >>". print_r($data, true));
        return $data;
    }
    public function getPostCategoryIds($post_ids)
    {
        if (empty($post_ids)) {
            \error_log("No valid post ID provided.");
            return [];
        }
        // Fetch categories for the post
        $category_ids = wp_get_post_categories($post_ids);
        \error_log("Category IDs for post : " . \print_r($category_ids, \true));
        // Check if categories are fetched
        if (empty($category_ids)) {
            \error_log("No categories found for post ID: " . $post_ids);
            return [];
        }
        // Format categories with UIDs
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
