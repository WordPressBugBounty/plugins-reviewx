<?php

namespace Rvx\Handlers\BulkAction;

use Rvx\Services\CacheServices;
class RegisterBulkActionsForReviewsHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($new_status, $old_status, $comment)
    {
        $screen = \get_current_screen();
        if ($screen->id !== 'edit-comments') {
            return;
        }
        if (isset($_REQUEST['action']) && $_REQUEST['action'] != -1) {
            $doaction = $_REQUEST['action'];
        } elseif (isset($_REQUEST['action2']) && $_REQUEST['action2'] != -1) {
            $doaction = $_REQUEST['action2'];
        } else {
            return;
        }
        // Check if we are in the comments admin page
        if (!isset($_REQUEST['comment'])) {
            return;
        }
        $comment_ids = $_REQUEST['comment'];
        // Process each bulk action
        if ($doaction === 'approve') {
            foreach ($comment_ids as $comment_id) {
                // Approve the comment
                wp_set_comment_status($comment_id, 'approve');
            }
            $redirect_to = add_query_arg('bulk_approved_comments', \count($comment_ids), wp_get_referer());
            wp_safe_redirect($redirect_to);
            exit;
        }
        if ($doaction === 'unapprove') {
            foreach ($comment_ids as $comment_id) {
                // Unapprove the comment
                wp_set_comment_status($comment_id, 'hold');
            }
            $redirect_to = add_query_arg('bulk_unapproved_comments', \count($comment_ids), wp_get_referer());
            wp_safe_redirect($redirect_to);
            exit;
        }
        if ($doaction === 'spam') {
            foreach ($comment_ids as $comment_id) {
                // Mark the comment as spam
                wp_spam_comment($comment_id);
            }
            $redirect_to = add_query_arg('bulk_spam_comments', \count($comment_ids), wp_get_referer());
            wp_safe_redirect($redirect_to);
            exit;
        }
        if ($doaction === 'trash') {
            foreach ($comment_ids as $comment_id) {
                // Move the comment to trash
                wp_trash_comment($comment_id);
            }
            $redirect_to = add_query_arg('bulk_trash_comments', \count($comment_ids), wp_get_referer());
            wp_safe_redirect($redirect_to);
            exit;
        }
        $this->cacheServices->removeCache();
    }
}
