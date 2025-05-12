<?php

namespace Rvx\Handlers;

class CommentStatusHandler
{
    public function __invoke($comment_id)
    {
        $comments = get_comment($comment_id);
        \error_log("Comment id" . $comments);
    }
}
