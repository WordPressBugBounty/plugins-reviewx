<?php

namespace Rvx\Handlers;

use Exception;
use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\CacheServices;
class WooReviewTableHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($new_status, $old_status, $comment)
    {
        $screen = \get_current_screen();
        if ($screen instanceof \Rvx\Handlers\WP_Screen || $screen->id == 'edit-comments') {
            $comment_id = $comment->comment_ID;
            $this->handleAction($new_status, $old_status, $comment_id);
        }
        if (wp_doing_ajax()) {
            $comment_id = $comment->comment_ID;
            $this->handleAction($new_status, $old_status, $comment_id);
        }
    }
    public function handleAction($new_status, $old_status, $comment_id)
    {
        $wpUniqueId = $this->getWpUniqueId($comment_id);
        switch (\true) {
            case $this->isMoveToTrash($new_status, $old_status):
                $this->moveToTrash($wpUniqueId);
                break;
            case $this->isRestoreFromTrash($new_status, $old_status):
                $this->restoreFromTrash($wpUniqueId, $new_status);
                break;
            default:
                $this->changeVisibility($new_status, $old_status, $wpUniqueId);
                break;
        }
        $this->cacheServices->removeCache();
    }
    /**
     * Generate the unique ID for a WordPress comment.
     */
    private function getWpUniqueId($comment_id)
    {
        return Client::getUid() . "-" . $comment_id;
    }
    /**
     * Check if the transition is moving to Trash from approved, unapproved, or spam.
     */
    private function isMoveToTrash($new_status, $old_status)
    {
        return $new_status === "trash";
    }
    /**
     * Check if the transition is restoring from Trash to approved or unapproved.
     */
    private function isRestoreFromTrash($new_status, $old_status)
    {
        return $old_status === "trash";
    }
    /**
     * Move a review to Trash.
     */
    private function moveToTrash($wpUniqueId) : void
    {
        try {
            $data = ["WpUniqueId" => $wpUniqueId];
            (new ReviewsApi())->reviewMoveToTrash($data);
        } catch (Exception $e) {
            \error_log("Move to trash : " . \print_r($e->getMessage(), \true));
        }
    }
    /**
     * Restore a review from Trash to a given status.
     */
    private function restoreFromTrash($wpUniqueId, $new_status)
    {
        try {
            (new ReviewsApi())->restoreReview($wpUniqueId);
        } catch (Exception $e) {
            \error_log("Restored Form trash " . \print_r($e->getMessage(), \true));
        }
    }
    /**
     * Mark a review as spam.
     */
    private function changeVisibility($new_status, $old_status, $wpUniqueId)
    {
        try {
            $statusMap = ["approved" => 1, "published" => 1, "unapproved" => 4, "unpublished" => 2, "pending" => 4, "spam" => 5];
            $status = $statusMap[$new_status];
            (new ReviewsApi())->visibilityReviewData(["status" => $status], $wpUniqueId);
        } catch (Exception $e) {
            \error_log("Change Visibility: " . \print_r($e->getMessage(), \true));
        }
    }
}
