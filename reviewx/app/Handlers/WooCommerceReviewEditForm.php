<?php

namespace Rvx\Handlers;

use Rvx\Utilities\Auth\Client;
use Rvx\Services\ReviewService;
class WooCommerceReviewEditForm
{
    public function __invoke($id, $data)
    {
        $updatedData = $this->prepareData($id, $data);
        $reviewService = new ReviewService();
        $response = $reviewService->updateWooReview($updatedData, $data);
    }
    public function prepareData($id, $data)
    {
        return ['wp_id' => $id, 'wp_post_id' => $data['comment_post_ID'], 'comment_approved' => $data['comment_approved'], 'rating' => get_comment_meta($id, 'rating', \true), 'reviewer_email' => $data['comment_author_email'], 'reviewer_name' => $data['comment_author'], 'feedback' => $data['comment_content'], 'date' => current_time('mysql', \true), 'customer_id' => $data['user_id'], 'wp_unique_id' => Client::getUid() . '-' . $id, 'woocommerce_update' => \true];
    }
}
