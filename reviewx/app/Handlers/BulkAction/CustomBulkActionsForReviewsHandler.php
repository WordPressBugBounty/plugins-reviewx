<?php

namespace Rvx\Handlers\BulkAction;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
class CustomBulkActionsForReviewsHandler
{
    public function __invoke($screen)
    {
        if (isset($screen) && $screen['id'] !== 'edit-comments') {
            return;
        }
        if (isset($_REQUEST['action']) && isset($_REQUEST['delete_comments'])) {
            $action = $_REQUEST['action'];
            $comment_ids = \array_map('intval', $_REQUEST['delete_comments']);
            $valid_actions = ['approve', 'unapprove', 'spam', 'trash'];
            $data = [];
            if (\in_array($action, $valid_actions)) {
                if ('approve' == $action) {
                    $data['status'] = 1;
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkUpdate($data);
                }
                if ('unapprove' == $action) {
                    $data['status'] = 2;
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkUpdate($data);
                }
                if ('spam' == $action) {
                    $data['status'] = 3;
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkUpdate($data);
                }
                if ('trash' == $action) {
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkTrash($data);
                }
                if ('unspam' == $action) {
                    $data['status'] = 1;
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkTrash($data);
                }
                if ('restore' == $action) {
                    $data['status'] = 1;
                    $data = ['review_wp_unique_ids' => $this->modifiyIds($comment_ids)];
                    (new ReviewsApi())->reviewBulkTrash($data);
                }
            }
        }
    }
    public function modifiyIds($comment_ids)
    {
        $modify = \array_map(function ($id) {
            return Client::getUid() . '-' . $id;
        }, $comment_ids);
        return $modify;
    }
}
