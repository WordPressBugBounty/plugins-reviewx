<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
class WoocommerceCommentMoveToTrashHandler
{
    public function __invoke($comment_id)
    {
        \error_log("data 123 >>" . $comment_id);
        $wpUniqueId = Client::getUid() . '-' . $comment_id;
    }
}
