<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Api\ReviewsApi;
use ReviewX\CPT\CptHelper;
class CacheServices extends \ReviewX\Services\Service
{
    public const PENDING_REVIEW_NOTICE_SUMMARY_TRANSIENT = 'rvx_pending_review_notice_summary';
    public const PENDING_REVIEW_NOTICE_SYNC_HOOK = 'reviewx_pending_review_notice_sync';
    public function currentUserCanAccessReviewx() : bool
    {
        return \is_user_logged_in() && (\current_user_can('manage_options') || \current_user_can('edit_others_posts') || \current_user_can('manage_woocommerce'));
    }
    private function defaultPendingReviewNoticeSummary() : array
    {
        return ['all' => 0, 'published' => 0, 'unpublished' => 0, 'archive' => 0, 'pending' => 0, 'spam' => 0, 'trash' => 0, 'generated_at' => \current_time('mysql', \true)];
    }
    private function normalizePendingReviewNoticeSummary(array $summary) : array
    {
        $defaults = $this->defaultPendingReviewNoticeSummary();
        foreach (['all', 'published', 'unpublished', 'archive', 'pending', 'spam', 'trash'] as $key) {
            $defaults[$key] = \max(0, (int) ($summary[$key] ?? 0));
        }
        if (!empty($summary['generated_at']) && \is_string($summary['generated_at'])) {
            $defaults['generated_at'] = $summary['generated_at'];
        }
        return $defaults;
    }
    public function pendingReviewNoticeSummaryFromCache() : array
    {
        $summary = \get_transient(self::PENDING_REVIEW_NOTICE_SUMMARY_TRANSIENT);
        if (\is_array($summary)) {
            return $this->normalizePendingReviewNoticeSummary($summary);
        }
        return $this->defaultPendingReviewNoticeSummary();
    }
    public function refreshPendingReviewNoticeSummary() : array
    {
        $fallbackSummary = $this->pendingReviewNoticeSummaryFromCache();
        if (!\ReviewX\Utilities\Auth\Client::has()) {
            return $fallbackSummary;
        }
        $response = (new ReviewsApi())->statusSummary();
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $summary = $this->normalizePendingReviewNoticeSummary($response->getApiData());
            \set_transient(self::PENDING_REVIEW_NOTICE_SUMMARY_TRANSIENT, $summary, HOUR_IN_SECONDS);
            return $summary;
        }
        return $fallbackSummary;
    }
    public function pendingReviewNoticeSummary(bool $forceRefresh = \false) : array
    {
        if ($forceRefresh) {
            return $this->refreshPendingReviewNoticeSummary();
        }
        $summary = \get_transient(self::PENDING_REVIEW_NOTICE_SUMMARY_TRANSIENT);
        if (\is_array($summary)) {
            return $this->normalizePendingReviewNoticeSummary($summary);
        }
        return $this->refreshPendingReviewNoticeSummary();
    }
    private function resolveCommentStatuses(string $status) : array
    {
        $normalizedStatus = \strtolower(\trim($status));
        switch ($normalizedStatus) {
            case '1':
            case 'approve':
            case 'approved':
            case 'publish':
            case 'published':
                return ['1', 'approve'];
            case '0':
            case 'hold':
            case 'pending':
            case 'unapproved':
                return ['0', 'hold'];
            case 'spam':
                return ['spam'];
            case 'trash':
                return ['trash'];
            default:
                return [$status];
        }
    }
    private function countTopLevelReviewsByStatus(string $status) : int
    {
        global $wpdb;
        $postTypes = (new CptHelper())->usedCPTOnSync('used');
        if (!\is_array($postTypes)) {
            $postTypes = [];
        }
        $postTypes[] = 'product';
        $postTypes = \array_values(\array_unique(\array_filter(\array_map('sanitize_key', $postTypes))));
        $statuses = \array_values(\array_unique($this->resolveCommentStatuses($status)));
        if (empty($postTypes) || empty($statuses)) {
            return 0;
        }
        $statusPlaceholders = \implode(', ', \array_fill(0, \count($statuses), '%s'));
        $placeholders = \implode(', ', \array_fill(0, \count($postTypes), '%s'));
        $sql = "\n            SELECT COUNT(c.comment_ID)\n            FROM {$wpdb->comments} c\n            INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID\n            WHERE c.comment_parent = 0\n              AND c.comment_approved IN ({$statusPlaceholders})\n              AND p.post_type IN ({$placeholders})\n              AND (\n                  c.comment_type IS NULL\n                  OR c.comment_type IN ('', 'comment', 'review')\n              )\n        ";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is a static query skeleton with runtime-generated placeholders; all user-controlled values are passed via prepare().
        $prepared = $wpdb->prepare($sql, \array_merge($statuses, $postTypes));
        if (!\is_string($prepared)) {
            return 0;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared dynamic post type list query for accurate top-level review counts.
        return (int) $wpdb->get_var($prepared);
    }
    public function allReviewApproveCount() : int
    {
        return $this->countTopLevelReviewsByStatus('1');
    }
    public function allReviewPendingCount() : int
    {
        return $this->countTopLevelReviewsByStatus('0');
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
        return $this->countTopLevelReviewsByStatus('spam');
    }
    public function allReviewTrashCount() : int
    {
        return $this->countTopLevelReviewsByStatus('trash');
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
        \delete_transient('rvx_ping_cache');
        \wp_cache_delete('rvx_dashboard_site_data', 'reviewx');
        \wp_cache_delete('rvx_site_uid', 'reviewx');
        \wp_cache_delete('rvx_wc_data_exists', 'reviewx');
        \wp_cache_delete('rvx_sites_table_exists', 'reviewx');
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
