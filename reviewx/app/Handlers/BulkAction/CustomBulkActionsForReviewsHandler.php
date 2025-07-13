<?php

namespace Rvx\Handlers\BulkAction;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\CacheServices;
class CustomBulkActionsForReviewsHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke()
    {
        $screen = \get_current_screen();
        // Ensure we are on the 'edit-comments' screen
        if (!$screen instanceof \Rvx\Handlers\BulkAction\WP_Screen || $screen->id !== 'edit-comments') {
            return;
        }
        // Validate request parameters
        if (empty($_REQUEST['action']) || empty($_REQUEST['delete_comments'])) {
            return;
        }
        $action = sanitize_text_field($_REQUEST['action']);
        $comment_ids = \array_map('intval', (array) $_REQUEST['delete_comments']);
        // Define valid actions
        $valid_actions = ['approve', 'unapprove', 'spam', 'trash', 'unspam', 'restore'];
        if (!\in_array($action, $valid_actions, \true)) {
            return;
        }
        $data = [];
        $reviewApi = new ReviewsApi();
        // Process actions
        switch ($action) {
            case 'approve':
                $data['status'] = 1;
                $data['review_wp_unique_ids'] = $this->modifiyIds($comment_ids);
                $reviewApi->reviewBulkUpdate($data);
                break;
            case 'unapprove':
                $data['status'] = 2;
                $data['review_wp_unique_ids'] = $this->modifiyIds($comment_ids);
                $reviewApi->reviewBulkUpdate($data);
                break;
            case 'spam':
                $data['status'] = 3;
                $data['review_wp_unique_ids'] = $this->modifiyIds($comment_ids);
                $reviewApi->reviewBulkUpdate($data);
                break;
            case 'trash':
                $data['review_wp_unique_ids'] = $this->modifiyIds($comment_ids);
                $reviewApi->reviewBulkTrash($data);
                break;
            case 'unspam':
            case 'restore':
                $data['status'] = 1;
                $data['review_wp_unique_ids'] = $this->modifiyIds($comment_ids);
                $reviewApi->reviewBulkTrash($data);
                break;
        }
        $this->cacheServices->removeCache();
    }
    public function modifiyIds($comment_ids)
    {
        return \array_map(function ($id) {
            return Client::getUid() . '-' . $id;
        }, $comment_ids);
    }
}
