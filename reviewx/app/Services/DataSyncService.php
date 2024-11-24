<?php

namespace Rvx\Services;

use Rvx\Models\Site;
use Rvx\Api\DataSyncApi;
use Rvx\Services\OrderService;
use Rvx\Utilities\Auth\Client;
class DataSyncService extends \Rvx\Services\Service
{
    protected OrderService $orderService;
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    public function dataSync($from)
    {
        \date_default_timezone_set('UTC');
        $now = \microtime(\true);
        $dateTime = \DateTime::createFromFormat('U.u', \sprintf('%.6F', $now));
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s.u');
        \error_log("Start Time At " . $formattedDateTime);
        $categories = $this->fetchCategories();
        if (\class_exists('WooCommerce')) {
            $products = $this->fetchProducts();
        }
        $allPostWithOutProduct = $this->fetchNonProductPosts();
        $file_path = RVX_DIR_PATH . 'sync.jsonl';
        if ($this->dataWriteTojsonl($categories, $products, $allPostWithOutProduct, $file_path)) {
            $file_info = $this->prepareFileInfo($file_path);
            $file = $_FILES['file'] = $file_info;
            $fileUpload = (new DataSyncApi())->dataSync($file, $from);
            if (\file_exists($file_path)) {
                \unlink($file_path);
            }
            return $fileUpload;
        }
    }
    private function fetchCategories()
    {
        $categories = get_categories(array('hide_empty' => \false, 'taxonomy' => ['product_cat', 'category', 'product_type', 'product_visibility']));
        return $categories;
    }
    private function fetchProducts()
    {
        $args = ['post_type' => 'product', 'posts_per_page' => -1, 'post_status' => ['publish', 'trash', 'private']];
        $posts = get_posts($args);
        $products = [];
        foreach ($posts as $post) {
            $product = wc_get_product($post->ID);
            if ($product) {
                $products[] = $product;
            }
        }
        return $products;
    }
    private function fetchNonProductPosts()
    {
        $args = ['public' => \true, '_builtin' => \false];
        $custom_post_types = \array_diff(get_post_types($args, 'names'), ['product']);
        $all_custom_posts = \array_reduce($custom_post_types, function ($carry, $post_type) {
            $posts = get_posts(['post_type' => $post_type, 'posts_per_page' => -1]);
            return \array_merge($carry, $posts);
        }, []);
        return $all_custom_posts;
    }
    private function dataWriteTojsonl($categories, $products, $allPostWithOutProduct, $file_path)
    {
        $file = \fopen($file_path, 'w');
        if ($file) {
            foreach ($categories as $category) {
                $category_data = $this->formatCategoryData($category);
                \fwrite($file, \json_encode($category_data) . \PHP_EOL);
            }
            foreach ($allPostWithOutProduct as $post) {
                $post = $this->formaPostData($post);
                \fwrite($file, \json_encode($post) . \PHP_EOL);
            }
            if (\class_exists('WooCommerce')) {
                foreach ($products as $product) {
                    $product_data = $this->formatProductData($product);
                    \fwrite($file, \json_encode($product_data) . \PHP_EOL);
                }
                foreach ($this->getAllFormattedOrders() as $order) {
                    \fwrite($file, \json_encode($order) . \PHP_EOL);
                }
            }
            foreach ($this->getAllFormattedUsers() as $user) {
                \fwrite($file, \json_encode($user) . \PHP_EOL);
            }
            foreach ($this->getAllFormattedComments() as $review) {
                \fwrite($file, \json_encode($review) . \PHP_EOL);
            }
            \fclose($file);
            return \true;
        }
        return \false;
    }
    private function formatCategoryData($category)
    {
        return ['rid' => 'rid://Category/' . $category->term_id, 'wp_id' => $category->term_id, 'title' => $category->name, 'slug' => $category->slug, 'taxonomy' => $category->taxonomy, 'description' => $category->description, 'parent_wp_unique_id' => Client::getUid() . '-' . $category->parent];
    }
    private function prepareFileInfo($file_path)
    {
        return ['name' => \basename($file_path), 'full_path' => \realpath($file_path), 'type' => \mime_content_type($file_path), 'tmp_name' => $file_path, 'error' => 0, 'size' => \filesize($file_path)];
    }
    public function formatProductData($product)
    {
        $image_url = $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : null;
        return ['rid' => 'rid://Product/' . $product->get_id(), "wp_id" => $product->get_id(), "title" => $product->get_name(), "url" => $product->get_permalink(), "description" => $product->get_short_description(), "price" => (float) $product->get_regular_price(), "discounted_price" => (float) $product->get_sale_price(), "slug" => $product->get_slug(), "image" => $image_url ?? null, "status" => $this->productStatus($product->get_status()), "post_type" => get_post_type($product->get_id()), "total_reviews" => (int) $product->get_review_count() ?? 0, "avg_rating" => (float) $product->get_average_rating() ?? 0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "modified_date" => $product->post_modified ?? null, "category_ids" => $this->productCategory($product)];
    }
    public function formaPostData($post)
    {
        $image_url = get_the_post_thumbnail_url($post->ID, 'full') ? get_the_post_thumbnail_url($post->ID, 'full') : null;
        return ['rid' => 'rid://Product/' . $post->ID, "wp_id" => $post->ID, "title" => $post->post_title, "url" => get_permalink($post->ID), "description" => $post->post_excerpt, "price" => 0, "discounted_price" => 0, "slug" => $post->post_name, "image" => $image_url ?? null, "status" => $this->productStatus($post->post_status), "post_type" => get_post_type($post->ID), "total_reviews" => get_comments_number($post->ID) ?? 0, "avg_rating" => 0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "deleted_at" => $post->post_modified ?? null, "category_ids" => $this->getPostCategoryIds($post->ID)];
    }
    public function getPostCategoryIds($post_ids)
    {
        $category_ids = wp_get_post_categories($post_ids);
        $parent_category_ids = [];
        foreach ($category_ids as $category_id) {
            $parent_category_ids[] = $category_id;
        }
        return $parent_category_ids;
    }
    public function productStatus($status)
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'private':
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
                $parent_category_ids[] = $category_id;
            }
        }
        return $parent_category_ids;
    }
    public function postCategory($product)
    {
        $product_categories = $product->get_category_ids();
        $parent_category_ids = array();
        foreach ($product_categories as $category_id) {
            $category = get_term($category_id, 'category');
            if ($category && $category->parent == 0) {
                $parent_category_ids[] = $category_id;
            }
        }
        return $parent_category_ids;
    }
    private function fetchUsers()
    {
        $args = ['role__in' => ['customer'], 'number' => -1];
        $user_query = new \WP_User_Query($args);
        return $user_query->get_results();
    }
    public function getAllFormattedUsers()
    {
        $users = $this->fetchUsers();
        $formattedUsers = [];
        foreach ($users as $user) {
            $formattedUsers[] = $this->formatUserData($user);
        }
        return $formattedUsers;
    }
    public function formatUserData($user)
    {
        return [
            'rid' => 'rid://Customer/' . $user->ID,
            'wp_id' => $user->ID,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->user_email,
            // 'display_name' => $user->display_name,
            // 'role' => $user->roles,
            'avatar' => get_avatar_url($user->ID),
            'city' => get_user_meta($user->ID, 'billing_city', \true) ?? '',
            'phone' => get_user_meta($user->ID, 'billing_phone', \true) ?? '',
            'address' => get_user_meta($user->ID, 'billing_address_1', \true) ?? '',
            'country' => get_user_meta($user->ID, 'billing_country', \true) ?? '',
            'status' => 1,
        ];
    }
    private function fetchOrders()
    {
        $args = ['limit' => -1, 'status' => 'any', 'date_created' => '>' . (new \DateTime('-60 days'))->format('Y-m-d H:i:s')];
        return wc_get_orders($args);
    }
    public function getAllFormattedOrders()
    {
        $orders = $this->fetchOrders();
        $formattedOrders = [];
        foreach ($orders as $order) {
            $formattedOrders[] = $this->formatOrderData($order);
        }
        return $formattedOrders;
    }
    public function formatOrderData($order)
    {
        $status = $order->get_status();
        $customer = null;
        if (\is_a($order, 'WC_Order')) {
            $customer = $order->get_customer_id();
        }
        $wcOrderStat = $this->orderService->wooOrderState($order->get_id());
        $paid_at = $this->compleiteAtOrder($order);
        $status_mapping = $this->orderStatusArray();
        $orderData = ['rid' => 'rid://Order/' . $order->get_id(), "wp_id" => (int) $order->get_id(), "customer_id" => $customer !== null ? Client::getUid() . '-' . $customer : null, "subtotal" => (float) $order->get_subtotal(), "tax" => (float) $order->get_total_tax(), "total" => (float) $order->get_total(), "status" => $order->get_status(), "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "paid_at" => $wcOrderStat['date_paid'], 'created_at' => $order->get_date_created()->date('Y-m-d H:i:s'), 'updated_at' => \date('Y-m-d H:i:s'), 'order_items' => $this->getOrderItems($order)];
        if (isset($status_mapping[$status])) {
            $orderData[$status_mapping[$status]] = \date('Y-m-d H:i:s');
        }
        return $orderData;
    }
    public function orderStatusArray() : array
    {
        return ['processing' => 'processing_at', 'pending_payment' => 'pending_payment_at', 'on_hold' => 'on_hold_at', 'completed' => 'completed_at', 'cancelled' => 'cancelled_at', 'refunded' => 'refunded_at', 'failed' => 'failed_at', 'draft' => 'draft_at'];
    }
    public function getOrderItems($order)
    {
        $items = $order->get_items();
        $order_items = [];
        $wc_order_stat = $this->compleiteAtOrder($order);
        foreach ($items as $item_id => $item) {
            $product = $item->get_product();
            $product_image = $product ? get_the_post_thumbnail_url($product->get_id()) : '';
            $product = $item->get_product();
            $order_items[] = ['wp_id' => $item_id, 'product_wp_unique_id' => Client::getUid() . '-' . $item->get_product_id(), 'name' => $item->get_name(), 'quantity' => $item->get_quantity(), 'price' => $item->get_total(), 'review_id' => null, 'image' => $product_image, 'site_id' => Client::getSiteId(), 'fulfillment_status' => $wc_order_stat['fulfillment_status'], 'fulfilled_at' => $wc_order_stat['fulfilled_at'], 'reviewed_at' => null];
        }
        return $order_items;
    }
    public function compleiteAtOrder($order)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $wpWcOrderStats = $wpdb->get_row($query);
        $data = ['fulfillment_status' => null, 'fulfilled_at' => null];
        if ($wpWcOrderStats->date_completed) {
            $data['fulfillment_status'] = $order->get_status();
            $data['fulfilled_at'] = $wpWcOrderStats->date_completed;
        }
        if (!$wpWcOrderStats->date_completed && $wpWcOrderStats->date_paid) {
            $data['fulfillment_status'] = $order->get_status();
            $data['fulfilled_at'] = $wpWcOrderStats->date_paid;
        }
        return $data;
    }
    public function getAllFormattedComments()
    {
        $comments = $this->fetchAllComments();
        $formattedComments = [];
        foreach ($comments as $comment) {
            $formattedComments[] = $this->formatComment($comment);
        }
        return $formattedComments;
    }
    public function getAllCustomPostTypes()
    {
        $post_types = get_post_types([], 'objects');
        $custom_post_types = [];
        foreach ($post_types as $post_type) {
            if ($post_type->name !== 'page' && $post_type->name !== 'attachment' && $post_type->name !== 'revision' && $post_type->name !== 'nav_menu_item') {
                $custom_post_types[] = $post_type->name;
            }
        }
        return $custom_post_types;
    }
    private function fetchAllComments()
    {
        $statuses = ['0', '1', 'trash', 'spam'];
        $args = ['status' => $statuses, 'post_type' => $this->getAllCustomPostTypes(), 'number' => ''];
        $comments_query = new \WP_Comment_Query($args);
        $comments = $comments_query->get_comments();
        return \array_unique($comments, \SORT_REGULAR);
    }
    private function getCommentMeta($comment_id)
    {
        return get_comment_meta($comment_id);
    }
    private function extractRating($comment_meta)
    {
        return isset($comment_meta['rating'][0]) ? $comment_meta['rating'][0] : 0;
    }
    private function extractVerifiedStatus($comment_meta)
    {
        return isset($comment_meta['verified'][0]) && $comment_meta['verified'][0] == '1';
    }
    private function reviewTitle($comment_meta)
    {
        return isset($comment_meta['reviewx_title'][0]) ? $comment_meta['reviewx_title'][0] : null;
    }
    private function moreMetaData($comment_meta)
    {
        if (isset($comment_meta['is_recommended'][0])) {
            return $comment_meta['is_recommended'][0] === '1';
        }
        return \false;
    }
    private function anonymousData($comment_meta)
    {
        if (isset($comment_meta['is_anonymous'][0])) {
            return $comment_meta['is_anonymous'][0] === '1';
        }
        return \false;
    }
    private function attachments($comment_meta)
    {
        $attachments = isset($comment_meta['reviewx_attachments'][0]) ? $comment_meta['reviewx_attachments'][0] : '';
        $video_url = isset($comment_meta['reviewx_video_url'][0]) ? $comment_meta['reviewx_video_url'][0] : '';
        $data = \is_string($attachments) ? \unserialize($attachments) : '';
        if ($data !== \false && isset($data['images'])) {
            $links = [];
            foreach ($data['images'] as $i => $image_id) {
                $links[] = wp_get_attachment_url($image_id);
            }
            if ($video_url) {
                $links[] = $video_url;
            }
            return $links;
        }
    }
    public function getCommentStatus($comment)
    {
        switch ($comment->comment_approved) {
            case '1':
                return 'published';
            case '0':
                return 'pending';
            case 'spam':
                return 'spam';
            default:
                return 'unknown';
        }
    }
    private function formatComment($comment)
    {
        $meta_data = get_comment_meta($comment->comment_ID);
        $replies = $this->getCommentReplies($comment->comment_ID);
        $trashed_at = null;
        if ($comment->comment_approved === 'trash') {
            $status = get_comment_meta($comment->comment_ID, '_wp_trash_meta_status', \true) == 0 ? 'pending' : 'published';
            $metaTrashTime = get_comment_meta($comment->comment_ID, '_wp_trash_meta_time', \true);
            $trashed_at = $metaTrashTime ? \date('Y-m-d H:i:s', $metaTrashTime) : null;
        } else {
            $status = $this->getCommentStatus($comment);
        }
        return ['rid' => 'rid://Review/' . $comment->comment_ID, 'product_id' => $comment->comment_post_ID, 'wp_id' => $comment->comment_ID, 'wp_post_id' => $comment->comment_post_ID, 'rating' => $this->extractRating($meta_data), 'reviewer_email' => $comment->comment_author_email, 'reviewer_name' => $comment->comment_author, 'title' => \trim($this->reviewTitle($meta_data), '"') ?? null, 'feedback' => \trim($comment->comment_content, '"') ?? null, 'verified' => $this->extractVerifiedStatus($meta_data), 'reply' => $replies[0]['content'] ?? null, 'attachments' => $this->attachments($meta_data) ?? [], 'is_recommended' => $this->moreMetaData($meta_data), 'is_anonymous' => $this->anonymousData($meta_data), 'status' => $status, 'trashed_at' => $trashed_at, 'created_at' => $comment->comment_date, 'customer_id' => $comment->user_id, 'ip' => $comment->comment_author_IP, 'criterias' => $this->multiCriteria($meta_data)];
    }
    private function getCommentReplies($parent_comment_id)
    {
        $args = ['parent' => $parent_comment_id, 'status' => 'any', 'orderby' => 'comment_date', 'order' => 'ASC'];
        $comments_query = new \WP_Comment_Query($args);
        $replies = $comments_query->get_comments();
        $formatted_replies = [];
        foreach ($replies as $reply) {
            $formatted_replies[] = ['wp_id' => $reply->comment_ID, 'wp_parent_id' => $reply->comment_parent, 'reviewer_email' => $reply->comment_author_email, 'reviewer_name' => $reply->comment_author, 'content' => $reply->comment_content, 'created_at' => $reply->comment_date];
        }
        return $formatted_replies;
    }
    public function dataSynComplete($data)
    {
        return Site::where("is_saas_sync", 0)->update($data);
    }
    public function multiCriteria($comment_meta)
    {
        $reviewRatingData = isset($comment_meta['reviewx_rating'][0]) ? $comment_meta['reviewx_rating'][0] : \false;
        $process_data = \unserialize($reviewRatingData);
        $multCritria_data = get_option('_rx_option_review_criteria');
        if (empty($multCritria_data)) {
            return;
        }
        $mapped_array = [];
        foreach ($multCritria_data as $key => $value) {
            if (isset($process_data[$key])) {
                $mapped_array[$value] = (int) $process_data[$key];
            }
        }
        $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        $newArray = \array_fill_keys($keys, 0);
        $i = 0;
        foreach ($mapped_array as $value) {
            if (isset($keys[$i])) {
                $newArray[$keys[$i]] = $value;
            }
            $i++;
        }
        if ($newArray == []) {
            return $newArray = null;
        }
        return $newArray;
    }
}
