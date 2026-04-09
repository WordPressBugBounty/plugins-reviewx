<?php

namespace ReviewX\Handlers\BulkAction;

use ReviewX\CPT\CptHelper;
use ReviewX\Services\CacheServices;
use WP_Screen;
class CustomBulkActionsForReviewsHandler
{
    protected $cacheServices;
    protected CptHelper $cptHelper;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
        $this->cptHelper = new CptHelper();
    }
    public function __invoke($actions)
    {
        $screen = \get_current_screen();
        if (!$screen instanceof WP_Screen || $screen->id !== 'edit-comments') {
            return $actions;
        }
        if (!$this->isReviewxCommentsScreen()) {
            return $actions;
        }
        $comment_status = isset($_REQUEST['comment_status']) ? sanitize_key(\wp_unslash($_REQUEST['comment_status'])) : 'all';
        if (isset($actions['unapprove'])) {
            $actions['unapprove'] = \__('Mark as Pending', 'reviewx');
        }
        if (isset($actions['approve'])) {
            $actions['approve'] = \__('Publish', 'reviewx');
        }
        if ($comment_status === 'trash') {
            return ['rvx_restore_publish' => \__('Restore as Published', 'reviewx'), 'rvx_restore_pending' => \__('Restore as Pending', 'reviewx'), 'rvx_restore_spam' => \__('Restore as Spam', 'reviewx'), 'delete' => $actions['delete'] ?? \__('Delete permanently')];
        }
        return $actions;
    }
    private function isReviewxCommentsScreen() : bool
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        $post_type = isset($_REQUEST['post_type']) ? sanitize_key(\wp_unslash($_REQUEST['post_type'])) : '';
        $comment_type = isset($_REQUEST['comment_type']) ? sanitize_key(\wp_unslash($_REQUEST['comment_type'])) : '';
        if ($comment_type === 'review') {
            return \true;
        }
        return !empty($post_type) && \in_array($post_type, $enabled_post_types, \true);
    }
}
