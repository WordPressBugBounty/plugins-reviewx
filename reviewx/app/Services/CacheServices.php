<?php

namespace Rvx\Services;

use Exception;
class CacheServices extends \Rvx\Services\Service
{
    public function allReviewApproveCount() : int
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) \n             FROM {$wpdb->comments} \n             WHERE comment_approved = '1' \n             AND comment_parent = 0 \n             AND comment_type IN ('review','comment')");
        return (int) $wpdb->get_var($query);
    }
    public function allReviewPendingCount() : int
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) \n        FROM {$wpdb->comments} \n        WHERE comment_approved = '0' \n        AND comment_parent = 0\n        AND comment_type IN ('review','comment')");
        return (int) $wpdb->get_var($query);
    }
    public function saasStatusReviewCount()
    {
        $data = \get_transient('reviews_data_list');
        if (\is_array($data)) {
            return $data['count'];
        }
        return [];
    }
    public function makeSaaSCallDecision()
    {
        $approveReviewCount = $this->allReviewApproveCount();
        $pendingReviewCount = $this->allReviewPendingCount();
        $saasApproveReviewCount = \array_key_exists('published', $this->saasStatusReviewCount()) ? $this->saasStatusReviewCount()['published'] : 0;
        $saasPendingReviewCount = \array_key_exists('pending', $this->saasStatusReviewCount()) ? $this->saasStatusReviewCount()['pending'] : 0;
        if ($approveReviewCount != $saasApproveReviewCount) {
            return \true;
        }
        if ($saasPendingReviewCount != $pendingReviewCount) {
            return \true;
        }
        return \false;
    }
    public function removeCache()
    {
        \delete_transient('reviews_data_list');
        \delete_transient('review_approve_data');
        \delete_transient('review_pending_data');
        \delete_transient('review_spam_data');
        \delete_transient('review_trash_data');
        \delete_transient('reviewx_aggregation');
        \delete_transient('review_shortcode');
        \delete_transient('_rvx_shortcode_transient');
        \delete_transient('rvx_shortcode_all_reviews');
    }
    public function clearShortcodesCache($arrayFirst, $arraySecond)
    {
        if (empty($arrayFirst)) {
            return \false;
        }
        $firstData = maybe_unserialize($arrayFirst);
        if (!\is_array($firstData) || !\is_array($arraySecond)) {
            return \false;
        }
        \ksort($firstData);
        \ksort($arraySecond);
        $firstHash = \md5(\json_encode($firstData));
        $secondHash = \md5(\json_encode($arraySecond));
        if ($firstHash === $secondHash) {
            return \true;
        }
        return \false;
    }
}
