<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
class WoocommerceCommentStatusChangeHandler
{
    public function __invoke($comment_id, $status)
    {
        $screen = \get_current_screen();
        if ($screen instanceof \Rvx\Handlers\WP_Screen || $screen->id == "edit-comments") {
            $this->prepareData($comment_id, $status);
        }
        if (wp_doing_ajax()) {
            $this->prepareData($comment_id, $status);
        }
    }
    public function prepareData($comment_id, $status)
    {
        $comment = get_comment($comment_id);
        $post_type = get_post_type($comment->comment_post_ID);
        if ($post_type) {
            $data = [];
            switch ($status) {
                case "approve":
                    $data["status"] = 1;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . "-" . $comment_id);
                    break;
                case "hold":
                    $data["status"] = 4;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . "-" . $comment_id);
                    break;
                case "spam":
                    $data["status"] = 5;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . "-" . $comment_id);
                    break;
                case "1":
                    $data["status"] = 1;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . "-" . $comment_id);
                    break;
            }
        }
    }
}
