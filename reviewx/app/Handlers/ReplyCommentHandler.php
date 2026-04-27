<?php

namespace ReviewX\Handlers;

use ReviewX\Api\ReviewsApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Utilities\Helper;
use ReviewX\Services\CacheServices;
use ReviewX\Services\ImportExportServices;
class ReplyCommentHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($comment_id, $comment_approved, $commentdata)
    {
        if (ImportExportServices::shouldSuspendCommentSideEffects()) {
            return;
        }
        if ($commentdata['comment_parent'] > 0) {
            $parent_comment = \get_comment($commentdata['comment_parent']);
            if ($parent_comment) {
                $wpUniqueId = Client::getUid() . '-' . $parent_comment->comment_ID;
                $replies = ['reply' => $commentdata['comment_content'], 'wp_id' => $parent_comment->comment_ID, 'replied_at' => !empty($commentdata['comment_date_gmt']) ? $commentdata['comment_date_gmt'] : $commentdata['comment_date'] ?? null];
                (new ReviewsApi())->commentReply($replies, $wpUniqueId);
                $this->cacheServices->removeCache();
            }
        }
    }
}
