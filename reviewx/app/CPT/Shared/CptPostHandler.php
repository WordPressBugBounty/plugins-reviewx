<?php

namespace Rvx\CPT\Shared;

use Rvx\Api\ProductApi;
use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class CptPostHandler
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function __invoke($post_id, $post, $update)
    {
        // Ignore if called during autosave or automatic draft
        if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || \in_array($post->post_status, ['auto-draft', 'inherit'], \true)) {
            return;
        }
        // Define the target post types
        $enabled_post_types = $this->cptHelper->enabledCPT();
        $post_type = $post->post_type;
        $post_status = $post->post_status;
        if (!isset($enabled_post_types[$post_type])) {
            return;
        }
        if ($post_status === 'trash') {
            return;
        }
        // Check if the meta key 'rvx_sync_status' exists
        $is_new_sync = get_post_meta($post_id, 'rvx_sync_new_status', \true);
        if (!$is_new_sync) {
            // Handle new post/product
            $isSaved = $this->createHandler($post_id, $post);
            // Mark as processed to avoid treating it as a new post again
            if ($isSaved[0]) {
                $this->enableCommentsReviews($post_id);
                update_post_meta($post_id, 'rvx_sync_new_status', 1, \true);
            }
            if ($isSaved[1] !== 200) {
                // Handle post/product update
                $isSaved = $this->updateHandler($post_id, $post);
                // Mark as processed to avoid treating it as a new post again
                if ($isSaved[0]) {
                    $this->enableCommentsReviews($post_id);
                    update_post_meta($post_id, 'rvx_sync_new_status', 1, \true);
                    update_post_meta($post_id, 'rvx_sync_edit_status', 1, \true);
                }
            }
        } else {
            // Handle post/product update
            $isSaved = $this->updateHandler($post_id, $post);
            if ($isSaved[0]) {
                $this->enableCommentsReviews($post_id);
                update_post_meta($post_id, 'rvx_sync_new_status', 1, \true);
                update_post_meta($post_id, 'rvx_sync_edit_status', 1, \true);
            }
        }
    }
    public function createHandler($post_id, $post)
    {
        if ($post->post_type === 'product') {
            $payload = $this->createProductData($post_id, $post);
        } else {
            // Public -> custom post type
            $payload = $this->createPostData($post_id, $post);
        }
        $response = (new ProductApi())->create($payload);
        // error_log('Create Response: ' .print_r($response, true));
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Post Creation Failed: " . \print_r($response, \true));
        }
        if ($response->getStatusCode() === Response::HTTP_OK) {
            return [\true, $response->getStatusCode()];
        }
        return [\false, $response->getStatusCode()];
    }
    public function updateHandler($post_id, $post)
    {
        if ($post->post_type === 'product') {
            $payload = $this->updatedProductData($post_id, $post);
        } else {
            // Public -> custom post type
            $payload = $this->updatedPostData($post_id, $post);
        }
        $uid = Client::getUid() . '-' . $post_id;
        $response = (new ProductApi())->update($payload, $uid);
        // error_log( 'Update Response: ' .print_r($response, true));
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log("Post Update Failed: " . \print_r($response, \true));
        }
        if ($response->getStatusCode() === Response::HTTP_OK) {
            return [\true, $response->getStatusCode()];
        }
        return [\false, $response->getStatusCode()];
    }
    private function createProductData($product_id, $post)
    {
        // Get the product object
        $product = wc_get_product($product_id);
        // Initialize variables
        $product_images = [];
        // Get the main product image
        $image_id = $product->get_image_id();
        // Featured image ID
        if ($image_id) {
            $product_images = wp_get_attachment_image_src($image_id, 'full');
            // Full-size image URL
        }
        if (empty($product_images)) {
            $product_images = '';
        }
        // Get the regular price
        $price = $product->get_regular_price();
        if (empty($price)) {
            $price = 0.0;
        }
        // Get the sale price (if available)
        $discounted_price = $product->get_sale_price();
        // If no sale price exists, the discounted price is the regular price
        if (empty($discounted_price)) {
            $discounted_price = $price;
        }
        return ["wp_id" => $product_id, "title" => isset($post->post_title) ? \htmlspecialchars($post->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => get_permalink($product_id), "description" => isset($post->short_description) ? \htmlspecialchars($post->short_description, \ENT_QUOTES, 'UTF-8') : null, "price" => $price, "discounted_price" => $discounted_price, "slug" => $post->post_name, "image" => $product_images[0] ?? (string) '', "status" => $this->postStatus($post->post_status), "post_type" => $post->post_type, "total_reviews" => (int) $product->get_review_count(), "avg_rating" => (float) $product->get_average_rating(), "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->getCategoryIds($product_id)];
    }
    private function updatedProductData($product_id, $post)
    {
        // Get the product object
        $product = wc_get_product($product_id);
        // Initialize variables
        $product_images = [];
        // Get the main product image
        $image_id = $product->get_image_id();
        // Featured image ID
        if ($image_id) {
            $product_images = wp_get_attachment_image_src($image_id, 'full');
            // Full-size image URL
        }
        if (empty($product_images)) {
            $product_images = '';
        }
        // Get the regular price
        $price = $product->get_regular_price();
        if (empty($price)) {
            $price = 0.0;
        }
        // Get the sale price (if available)
        $discounted_price = $product->get_sale_price();
        // If no sale price exists, the discounted price is the regular price
        if (empty($discounted_price)) {
            $discounted_price = $price;
        }
        return ["wp_id" => $product_id, "title" => isset($post->post_title) ? \htmlspecialchars($post->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => get_permalink($product_id), "description" => isset($post->post_excerpt) ? \htmlspecialchars($post->post_excerpt, \ENT_QUOTES, 'UTF-8') : null, "price" => (float) $price, "discounted_price" => (float) $discounted_price, "slug" => $post->post_name, "image" => $product_images[0] ?? (string) '', "status" => $this->postStatus($post->post_status), "post_type" => $post->post_type, "total_reviews" => (int) $product->get_review_count(), "avg_rating" => (float) $product->get_average_rating(), "category_wp_unique_ids" => $this->getCategoryIds($product_id)];
    }
    private function createPostData($post_id, $post)
    {
        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        if (empty($image_url)) {
            $image_url = '';
        }
        $average_rating = get_post_meta($post->ID, 'rvx_avg_rating', \true);
        if (empty($average_rating)) {
            $average_rating = 0.0;
        }
        $data = ["wp_id" => $post->ID, "title" => isset($post->post_title) ? \htmlspecialchars($post->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => get_permalink($post->ID), "description" => isset($post->post_excerpt) ? \htmlspecialchars($post->post_excerpt, \ENT_QUOTES, 'UTF-8') : null, "price" => 0, "discounted_price" => 0, "slug" => $post->post_name, "image" => (string) $image_url, "status" => $this->postStatus($post->post_status), "post_type" => get_post_type($post->ID), "total_reviews" => (int) get_comments_number($post->ID) ?? 0, "avg_rating" => (float) $average_rating, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->getCategoryIds($post_id)];
        return $data;
    }
    private function updatedPostData($post_id, $post)
    {
        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        if (empty($image_url)) {
            $image_url = '';
        }
        $average_rating = get_post_meta($post->ID, 'rvx_avg_rating', \true);
        if (empty($average_rating)) {
            $average_rating = 0.0;
        }
        $data = ["wp_id" => $post->ID, "title" => isset($post->post_title) ? \htmlspecialchars($post->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => get_permalink($post->ID), "description" => isset($post->post_excerpt) ? \htmlspecialchars($post->post_excerpt, \ENT_QUOTES, 'UTF-8') : null, "price" => 0, "discounted_price" => 0, "slug" => $post->post_name, "image" => (string) $image_url, "status" => $this->postStatus($post->post_status), "post_type" => get_post_type($post->ID), "total_reviews" => (int) get_comments_number($post->ID) ?? 0, "avg_rating" => (float) $average_rating, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->getCategoryIds($post_id)];
        return $data;
    }
    private function postStatus($status)
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
    private function getCategoryIds($post_id)
    {
        // Validate the post ID
        if (empty($post_id)) {
            \error_log("No valid post/product ID found.");
            return [];
        }
        // Get the post type
        $post_type = get_post_type($post_id);
        // Determine the taxonomy: 'product_cat' for products, hierarchical taxonomy for others
        $taxonomy = $post_type === 'product' ? 'product_cat' : null;
        if (!$taxonomy) {
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            foreach ($taxonomies as $key => $taxonomy_obj) {
                if ($taxonomy_obj->hierarchical) {
                    $taxonomy = $key;
                    break;
                }
            }
        }
        // Retrieve category IDs or assign default if none are found
        $category_ids = [];
        if ($taxonomy) {
            $category_ids = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
        }
        if (empty($category_ids)) {
            $category_ids = [0];
        }
        // Append the UID prefix to each category ID
        $uid = Client::getUid();
        $parent_category_ids = [];
        foreach ($category_ids as $category_id) {
            $parent_category_ids[] = $uid . '-' . $category_id;
        }
        return $parent_category_ids;
    }
    private function enableCommentsReviews($post_id)
    {
        global $wpdb;
        // Validate post ID
        $post = get_post($post_id);
        if (!$post) {
            return \false;
        }
        // Ensure the post type supports comments
        $post_type = $post->post_type;
        $supports = post_type_supports($post_type, 'comments');
        if (!$supports) {
            // Dynamically add comment support for the post type
            add_post_type_support($post_type, 'comments');
        }
        // Update the comment status in the database
        $updated = $wpdb->update(
            $wpdb->posts,
            ['comment_status' => 'open'],
            // Enable comments
            ['ID' => $post_id],
            // Match post ID
            ['%s'],
            // Data format for `comment_status`
            ['%d']
        );
        if ($updated === \false) {
            return \false;
        }
        // Clear WordPress cache for this post
        clean_post_cache($post_id);
        return \true;
    }
}
