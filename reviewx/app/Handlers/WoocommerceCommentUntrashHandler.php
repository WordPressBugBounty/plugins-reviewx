<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
class WoocommerceCommentUntrashHandler
{
    public function __invoke($comment_id)
    {
        \error_log("untrash >>>" . $comment_id);
    }
}
