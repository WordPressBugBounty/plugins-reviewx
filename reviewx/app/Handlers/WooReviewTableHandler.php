<?php

namespace ReviewX\Handlers;

use Exception;
use ReviewX\Api\ReviewsApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Services\CacheServices;
use WP_Screen;
class WooReviewTableHandler
{
    protected $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    public function __invoke($new_status, $old_status, $comment)
    {
        $this->handleAction($new_status, $old_status, $comment);
    }
    public function handleAction($new_status, $old_status, $comment)
    {
        $comment_id = $comment->comment_ID;
        $is_reply = $comment->comment_parent > 0;
        $sync_comment_id = $is_reply ? $comment->comment_parent : $comment_id;
        $wpUniqueId = $this->getWpUniqueId($sync_comment_id);
        if ($is_reply) {
            $this->handleReplyAction($new_status, $old_status, $wpUniqueId, $comment);
        } else {
            $this->handleReviewAction($new_status, $old_status, $wpUniqueId);
        }
        if ($comment && $comment->comment_post_ID) {
            \ReviewX\CPT\CptAverageRating::update_average_rating($comment->comment_post_ID);
        }
        $this->cacheServices->removeCache();
    }
    private function handleReplyAction($new_status, $old_status, $wpUniqueId, $comment)
    {
        try {
            $reviewsApi = new ReviewsApi();
            if ($new_status === 'trash' || $new_status === 'spam') {
                $reviewsApi->deleteCommentReply($wpUniqueId);
            } else {
                $replies = ['reply' => $comment->comment_content, 'wp_id' => $comment->comment_parent];
                $reviewsApi->commentReply($replies, $wpUniqueId);
            }
        } catch (Exception $e) {
            // Reply status sync failed
        }
    }
    private function handleReviewAction($new_status, $old_status, $wpUniqueId)
    {
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
            // Move to trash failed
        }
    }
    /**
     * Restore a review from Trash to a given status.
     */
    private function restoreFromTrash($wpUniqueId, $new_status)
    {
        try {
            $statusMap = ["approved" => 1, "unapproved" => 2, "hold" => 4, "pending" => 4, "spam" => 5];
            $status = isset($statusMap[$new_status]) ? $statusMap[$new_status] : 1;
            // Default to published if unknown
            (new ReviewsApi())->restoreReview($wpUniqueId, $status);
        } catch (Exception $e) {
            // Restored Form trash failed
        }
    }
    /**
     * Mark a review as spam.
     */
    private function changeVisibility($new_status, $old_status, $wpUniqueId)
    {
        try {
            $statusMap = [
                "approved" => 1,
                // WordPress default
                "published" => 1,
                "unapproved" => 2,
                // WordPress default
                "unpublished" => 2,
                "pending" => 4,
                "hold" => 4,
                "spam" => 5,
                "trash" => 3,
                "delete" => 3,
            ];
            if (!isset($statusMap[$new_status])) {
                return;
            }
            $status = $statusMap[$new_status];
            (new ReviewsApi())->visibilityReviewData(["status" => $status], $wpUniqueId);
        } catch (Exception $e) {
            // Change Visibility failed
        }
    }
}
