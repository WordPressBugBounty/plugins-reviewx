<?php

namespace ReviewX\Handlers\BulkAction;

use ReviewX\CPT\CptHelper;
use ReviewX\Services\CacheServices;
use WP_Screen;
class RegisterBulkActionsForReviewsHandler
{
    protected $cacheServices;
    protected CptHelper $cptHelper;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
        $this->cptHelper = new CptHelper();
    }
    public function __invoke($redirect_to, $doaction, $comment_ids)
    {
        $screen = \get_current_screen();
        if (!$screen instanceof WP_Screen || $screen->id !== 'edit-comments') {
            return $redirect_to;
        }
        if (!$this->isReviewxCommentsScreen()) {
            return $redirect_to;
        }
        $target_status = match ($doaction) {
            'rvx_restore_publish' => 'approve',
            'rvx_restore_pending' => 'hold',
            'rvx_restore_spam' => 'spam',
            default => null,
        };
        if ($target_status === null) {
            return $redirect_to;
        }
        foreach ((array) $comment_ids as $comment_id) {
            \wp_set_comment_status((int) $comment_id, $target_status);
        }
        $this->cacheServices->removeCache();
        return \add_query_arg(['rvx_bulk_restored' => $doaction, 'rvx_bulk_count' => \count((array) $comment_ids)], $redirect_to);
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
