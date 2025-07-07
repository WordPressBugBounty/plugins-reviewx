<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\CacheServices;
class ReplyCommentHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($comment_id, $comment_approved, $commentdata)
    {
        if ($commentdata['comment_parent'] > 0) {
            $parent_comment = get_comment($commentdata['comment_parent']);
            if ($parent_comment) {
                $wpUniqueId = Client::getUid() . '-' . $parent_comment->comment_ID;
                $replies = ['reply' => $commentdata['comment_content'], 'wp_id' => $parent_comment->comment_ID];
                (new ReviewsApi())->commentReply($replies, $wpUniqueId);
                $this->cacheServices->removeCache();
            }
        }
    }
}
