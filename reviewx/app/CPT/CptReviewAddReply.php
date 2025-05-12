<?php

namespace Rvx\CPT;

use Rvx\Services\ReviewService;
use Rvx\Utilities\Auth\Client;
class CptReviewAddReply
{
    public function __invoke($id, $data)
    {
        $updatedData = $this->prepareData($id, $data);
        $reviewService = new ReviewService();
        $response = $reviewService->updateReview($updatedData, $data);
    }
    public function prepareData($id, $data)
    {
        $post_id = $data['comment_post_ID'];
        $post_type = get_post_type($post_id);
        // Retrieve the rating.
        $rating = get_comment_meta($id, 'rating', \true);
        if ($post_type !== 'product') {
            // For other post types (non-product), round to two decimal places.
            $rating = (float) \round($rating, 2);
        }
        return ['wp_id' => $id, 'wp_post_id' => $post_id, 'comment_approved' => $data['comment_approved'], 'rating' => $rating, 'reviewer_email' => $data['comment_author_email'], 'reviewer_name' => $data['comment_author'], 'feedback' => $data['comment_content'], 'date' => current_time('mysql', \true), 'customer_id' => $data['user_id'], 'wp_unique_id' => Client::getUid() . '-' . $id, 'woocommerce_update' => \false];
    }
}
