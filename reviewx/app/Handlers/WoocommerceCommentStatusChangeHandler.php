<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
class WoocommerceCommentStatusChangeHandler
{
    public function __invoke($comment_id, $status)
    {
        $comment = get_comment($comment_id);
        $post_type = get_post_type($comment->comment_post_ID);
        if ($post_type) {
            $data = [];
            switch ($status) {
                case 'approve':
                    $data['status'] = 1;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . '-' . $comment_id);
                    break;
                case 'hold':
                    $data['status'] = 4;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . '-' . $comment_id);
                    break;
                case 'spam':
                    $data['status'] = 5;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . '-' . $comment_id);
                    break;
                case '1':
                    $data['status'] = 1;
                    (new ReviewsApi())->visibilityReviewData($data, Client::getUid() . '-' . $comment_id);
                    break;
            }
        }
    }
}
