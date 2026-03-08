<?php

namespace Rvx\Services;

\defined("ABSPATH") || exit;
class CacheServices extends \Rvx\Services\Service
{
    public function allReviewApproveCount() : int
    {
        $counts = \wp_count_comments();
        return (int) ($counts->approved ?? 0);
    }
    public function allReviewPendingCount() : int
    {
        $counts = \wp_count_comments();
        return (int) ($counts->moderated ?? 0);
    }
    public function saasStatusReviewCount()
    {
        $data = \get_transient('rvx_reviews_data_list');
        if (\is_array($data)) {
            return $data['count'];
        }
        return [];
    }
    public function allReviewSpamCount() : int
    {
        $counts = \wp_count_comments();
        return (int) ($counts->spam ?? 0);
    }
    public function allReviewTrashCount() : int
    {
        $counts = \wp_count_comments();
        return (int) ($counts->trash ?? 0);
    }
    public function makeSaaSCallDecision()
    {
        $approveReviewCount = $this->allReviewApproveCount();
        $pendingReviewCount = $this->allReviewPendingCount();
        $spamReviewCount = $this->allReviewSpamCount();
        $trashReviewCount = $this->allReviewTrashCount();
        $saasData = $this->saasStatusReviewCount();
        $saasApproveReviewCount = \array_key_exists('published', $saasData) ? $saasData['published'] : 0;
        $saasPendingReviewCount = \array_key_exists('pending', $saasData) ? $saasData['pending'] : 0;
        $saasSpamReviewCount = \array_key_exists('spam', $saasData) ? $saasData['spam'] : 0;
        $saasTrashReviewCount = \array_key_exists('trash', $saasData) ? $saasData['trash'] : 0;
        if ($approveReviewCount != $saasApproveReviewCount) {
            return \true;
        }
        if ($saasPendingReviewCount != $pendingReviewCount) {
            return \true;
        }
        if ($saasSpamReviewCount != $spamReviewCount) {
            return \true;
        }
        if ($saasTrashReviewCount != $trashReviewCount) {
            return \true;
        }
        return \false;
    }
    public function removeCache()
    {
        \delete_transient('rvx_reviews_data_list');
        \delete_transient('rvx_review_approve_data');
        \delete_transient('rvx_review_pending_data');
        \delete_transient('rvx_review_spam_data');
        \delete_transient('rvx_review_trash_data');
        \delete_transient('rvx_admin_aggregation');
        \delete_transient('rvx_review_shortcode');
        \delete_transient('rvx_shortcode_transient');
        \delete_transient('rvx_shortcode_all_reviews');
    }
    public function clearShortcodesCache($arrayFirst, $arraySecond)
    {
        if (empty($arrayFirst)) {
            return \false;
        }
        $firstData = \maybe_unserialize($arrayFirst);
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
    /**
     * Clear product-specific transients for reviews and insight data.
     * @param int $productId The WP Post ID of the product.
     */
    public function removeProductCache($productId) : void
    {
        if (!$productId) {
            return;
        }
        \delete_transient("rvx_{$productId}_latest_reviews");
        \delete_transient("rvx_{$productId}_latest_reviews_insight");
    }
}
