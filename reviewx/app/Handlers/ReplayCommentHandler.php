<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Api\UserApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class ReplayCommentHandler
{
    public function __construct()
    {
    }
    public function __invoke($comment_id, $comment_approved, $commentdata)
    {
        if ($commentdata['comment_parent'] > 0) {
            $parent_comment = get_comment($commentdata['comment_parent']);
            if ($parent_comment && $parent_comment->comment_type == 'review') {
                $wpUniqueId = Client::getUid() . '-' . $parent_comment->comment_ID;
                $replies = ['reply' => $commentdata['comment_content'], 'wp_id' => $parent_comment->comment_ID];
                $res = (new ReviewsApi())->commentReply($replies, $wpUniqueId);
                \error_log("Replay Status code " . $res->getStatusCode());
            }
        }
    }
}
