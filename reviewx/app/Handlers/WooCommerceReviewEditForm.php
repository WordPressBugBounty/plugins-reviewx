<?php

namespace ReviewX\Handlers;

use ReviewX\Services\ReviewService;
use ReviewX\Api\ReviewsApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Services\CacheServices;
class WooCommerceReviewEditForm
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($id, $data)
    {
        $comment = \get_comment($id);
        if ($comment && $comment->comment_parent > 0) {
            // It's a reply
            $wpUniqueId = Client::getUid() . '-' . $comment->comment_parent;
            $replies = ['reply' => $data['comment_content'], 'wp_id' => $comment->comment_parent];
            try {
                (new ReviewsApi())->commentReply($replies, $wpUniqueId);
            } catch (\Exception $e) {
                // Reply edit sync failed
            }
        } else {
            // It's a review
            $updatedData = $this->prepareData($id, $data);
            $reviewService = new ReviewService();
            $reviewService->updateWooReview($updatedData, $data);
        }
        $this->cacheServices->removeCache();
    }
    public function prepareData($id, $data)
    {
        $post_id = $data['comment_post_ID'];
        // $post_type = get_post_type($post_id);
        // Retrieve the rating.
        $rating = (float) \round(\get_comment_meta($id, 'rating', \true), 2);
        // if ($post_type === 'product') {
        //     // For products, round up to the nearest whole number.
        //     $rating = ceil((float) $rating);
        // } else {
        //     // For other post types, round to two decimal places.
        //     $rating = (float) round($rating, 2);
        // }
        return ['wp_id' => $id, 'wp_post_id' => $post_id, 'comment_approved' => $data['comment_approved'], 'rating' => $rating, 'reviewer_email' => $data['comment_author_email'], 'reviewer_name' => $data['comment_author'], 'feedback' => $data['comment_content'], 'date' => \current_time('mysql', \true), 'customer_id' => $data['user_id'], 'wp_unique_id' => Client::getUid() . '-' . $id, 'woocommerce_update' => \true];
    }
}
