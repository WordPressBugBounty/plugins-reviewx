<?php

namespace Rvx\Services\Wp;

use Rvx\Services\Service;
use WP_Error;
use WP_REST_Response;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class WpReviewService extends Service
{
    /**
     * @return Response
     */
    public function getReviews()
    {
        $reviews = $this->fetchReviews();
        $reviewsData = [];
        foreach ($reviews as $review) {
            $reviewData[] = $this->processReview($review);
            $repliesData[] = $this->fetchReplies($review->comment_ID);
            $reviewsData = ['reviews' => $reviewData, 'replies' => $repliesData];
        }
        return Helper::rest($reviewsData)->success("Successfully Fetched");
    }
    /**
     * @return mixed
     */
    protected function fetchReviews()
    {
        $args = ['type' => 'review', 'status' => ['approve', 'hold', 'spam'], 'parent' => 0, 'order' => 'DESC', 'no_found_rows' => \true];
        $reviewsQuery = new \WP_Comment_Query($args);
        return $reviewsQuery->get_comments();
    }
    /**
     * @param $review
     * @return array
     */
    protected function processReview($review) : array
    {
        $rating = get_comment_meta($review->comment_ID, 'rating', \true);
        $images = maybe_unserialize(get_comment_meta($review->comment_ID, 'rvx_comment_images', \true));
        $videos = maybe_unserialize(get_comment_meta($review->comment_ID, 'rvx_comment_video', \true));
        $review_title = get_comment_meta($review->comment_ID, 'rvx_title', \true);
        $likes = get_comment_meta($review->comment_ID, 'rvx_like', \true);
        $dislikes = get_comment_meta($review->comment_ID, 'rvx_dislik', \true);
        $productTitle = get_the_title($review->comment_post_ID);
        $attachments = ["images" => $images, "videos" => $videos];
        return ['id' => $review->comment_ID, 'reviewer_email' => $review->comment_author_email, 'reviewer_name' => $review->comment_author, 'title' => $review_title, 'product_title' => $productTitle, 'status' => $review->comment_approved, 'replied_at' => $review->comment_date, 'feedback' => $review->comment_content, 'rating' => $rating, 'attachments' => $attachments];
    }
    /**
     * @param $commentId
     * @return array
     */
    protected function fetchReplies($commentId) : array
    {
        $args = ['post_id' => $commentId, 'status' => 'approve', 'parent' => $commentId];
        $replyQuery = new \WP_Comment_Query();
        $replies = $replyQuery->query($args);
        $repliesData = [];
        foreach ($replies as $reply) {
            $repliesData[] = ['id' => $reply->comment_ID, 'content' => $reply->comment_content, 'author' => $reply->comment_author, 'replied_at' => $reply->comment_date];
        }
        return $repliesData;
    }
    public function createReview($params)
    {
        $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
        $rating = isset($params['rating']) ? sanitize_text_field($params['rating']) : '';
        $review_images = isset($params['review_images']) ? $params['review_images'] : array();
        $review_video = isset($params['review_video']) ? $params['review_video'] : array();
        $data = ['comment_post_ID' => isset($params['wp_post_id']) ? absint($params['wp_post_id']) : null, 'comment_content' => isset($params['feedback']) ? sanitize_text_field($params['feedback']) : '', 'comment_author' => get_current_user_id() ?? isset($params['reviewer_name']) ? sanitize_text_field($params['reviewer_name']) : 'asdf@dkf.com', 'comment_author_email' => isset($params['reviewer_email']) ? sanitize_text_field($params['reviewer_email']) : '', 'comment_type' => 'review', 'comment_approved' => isset($params['status']) ? sanitize_text_field($params['status']) : '', 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'comment_date' => current_time('mysql', \true)];
        $serialized_images = \serialize($review_images);
        $serialized_video = \serialize($review_video);
        try {
            $comment_id = wp_insert_comment($data);
            add_comment_meta($comment_id, 'rvx_comment_images', $serialized_images);
            add_comment_meta($comment_id, 'rvx_comment_video', $serialized_video);
            add_comment_meta($comment_id, 'rvx_comment_title', $title);
            add_comment_meta($comment_id, 'rvx_comment_rating', $rating);
            return Helper::rest(null)->success("Review Create");
        } catch (\Throwable $e) {
            return Helper::rest(null)->fails("No Data Found Found");
        }
    }
    public function updateReview($request)
    {
        $comment_id = $request['id'];
        $comment = get_comment($comment_id);
        if (!$comment) {
            return new WP_Error('invalid_comment_id', __('Invalid comment ID.', 'text-domain'), array('status' => 404));
        }
        $params = $request->get_params();
        // Extract review data from the request
        $product_id = isset($params['product_id']);
        $reviewer_name = isset($params['reviewer_name']);
        $reviewer_email = isset($params['reviewer_email']);
        $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
        $status = isset($params['status']) ? sanitize_text_field($params['status']) : '';
        $rating = isset($params['rating']) ? sanitize_text_field($params['rating']) : '';
        $review_content = isset($params['review_content']) ? sanitize_text_field($params['review_content']) : '';
        $review_images = isset($params['review_images']) ? $params['review_images'] : array();
        $review_video = isset($params['review_video']) ? $params['review_video'] : array();
        //Check if product ID is valid
        if (empty($product_id) || !wc_get_product($product_id)) {
            return new WP_REST_Response(array('error' => 'Invalid product ID'), 400);
        }
        $serialized_images = \serialize($review_images);
        $serialized_video = \serialize($review_video);
        $review_data = array(
            // 'comment_ID' => $comment_id ?? $comment->comment_ID,
            // 'comment_post_ID' => $product_id,
            'comment_content' => $review_content ?? $comment->comment_content,
            'comment_author' => $reviewer_name ?? $comment->comment_author,
            'comment_author_email' => $reviewer_email ?? $comment->comment_author_email,
            'comment_type' => 'review',
            'comment_approved' => $status ?? $comment->comment_approved,
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
            'comment_date_gmt' => current_time('mysql', 1),
            'comment_date' => current_time('mysql', \true),
        );
        // Insert the review into the database
        try {
            $comment_id = wp_update_comment($review_data);
            update_comment_meta($comment_id, 'rvx_comment_images', $serialized_images);
            update_comment_meta($comment_id, 'rvx_comment_video', $serialized_video);
            update_comment_meta($comment_id, 'rvx_comment_title', $title);
            update_comment_meta($comment_id, 'rvx_comment_rating', $rating);
            return Helper::rest(null)->success("Review Updated");
        } catch (\Throwable $e) {
            return Helper::rest(null)->fails("No Data Found Found");
        }
    }
}
