<?php

namespace Rvx\Handlers;

use Rvx\Api\UserApi;
use Rvx\WPDrill\Response;
class CommentStatusHandler
{
    public function __invoke($comment_id)
    {
        $comments = get_comment($comment_id);
        \error_log("Comment id" . $comments);
    }
}
