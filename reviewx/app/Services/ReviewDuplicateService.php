<?php

namespace ReviewX\Services;

\defined('ABSPATH') || exit;
use ReviewX\Api\ReviewsApi;
use ReviewX\CPT\CptHelper;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Utilities\Helper;
class ReviewDuplicateService extends \ReviewX\Services\Service
{
    private const SCAN_CACHE_KEY = 'rvx_duplicate_review_scan_report';
    private const SCAN_CACHE_TTL = 900;
    private \ReviewX\Services\CacheServices $cacheServices;
    private ReviewsApi $reviewsApi;
    public function __construct()
    {
        $this->cacheServices = new \ReviewX\Services\CacheServices();
        $this->reviewsApi = new ReviewsApi();
    }
    public function findDuplicateCommentId(array $commentData) : int
    {
        global $wpdb;
        $commentPostId = isset($commentData['comment_post_ID']) ? (int) $commentData['comment_post_ID'] : 0;
        $commentParent = isset($commentData['comment_parent']) ? (int) $commentData['comment_parent'] : 0;
        $normalizedIdentity = $this->normalizeIdentity(isset($commentData['comment_author']) ? (string) $commentData['comment_author'] : '', isset($commentData['comment_author_email']) ? (string) $commentData['comment_author_email'] : '');
        $normalizedContent = $this->normalizeCommentContent(isset($commentData['comment_content']) ? (string) $commentData['comment_content'] : '');
        if ($commentPostId <= 0 || $normalizedIdentity === '') {
            return 0;
        }
        $sql = $wpdb->prepare("SELECT comment_ID, comment_author, comment_author_email, comment_content\n             FROM {$wpdb->comments}\n             WHERE comment_post_ID = %d\n               AND comment_parent = %d\n               AND comment_approved != 'trash'\n             ORDER BY comment_date_gmt ASC, comment_ID ASC", $commentPostId, $commentParent);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Narrow duplicate lookup for a single post during import.
        $candidates = $wpdb->get_results($sql, ARRAY_A);
        foreach ($candidates as $candidate) {
            $candidateIdentity = $this->normalizeIdentity((string) ($candidate['comment_author'] ?? ''), (string) ($candidate['comment_author_email'] ?? ''));
            if ($candidateIdentity !== $normalizedIdentity) {
                continue;
            }
            $candidateContent = $this->normalizeCommentContent((string) ($candidate['comment_content'] ?? ''));
            if ($this->shouldTreatIncomingReviewAsDuplicate($normalizedContent, $candidateContent)) {
                return (int) $candidate['comment_ID'];
            }
        }
        return 0;
    }
    public function getDuplicateReviewGroups(bool $forceScan = \false) : array
    {
        if ($forceScan) {
            return $this->scanDuplicateReviewGroups();
        }
        $cachedReport = \get_transient(self::SCAN_CACHE_KEY);
        if (\is_array($cachedReport)) {
            return $cachedReport;
        }
        return $this->emptyReport(\false);
    }
    public function scanDuplicateReviewGroups() : array
    {
        $report = $this->buildDuplicateReviewGroups();
        \set_transient(self::SCAN_CACHE_KEY, $report, self::SCAN_CACHE_TTL);
        return $report;
    }
    public function removeDuplicateReviews(array $groupKeys = []) : array
    {
        $report = $this->buildDuplicateReviewGroups();
        $groups = $report['duplicate_groups'] ?? [];
        if (!empty($groupKeys)) {
            $allowedKeys = \array_fill_keys(\array_map('strval', $groupKeys), \true);
            $groups = \array_values(\array_filter($groups, static function (array $group) use($allowedKeys) {
                return isset($allowedKeys[$group['group_key']]);
            }));
        }
        if (empty($groups)) {
            return ['status' => 'success', 'message' => \__('No duplicate reviews found.', 'reviewx'), 'removed_reviews' => 0, 'processed_groups' => 0, 'removed_comment_ids' => [], 'saas_synced' => \true];
        }
        $duplicateIds = [];
        $affectedPosts = [];
        foreach ($groups as $group) {
            foreach ($group['duplicate_comment_ids'] ?? [] as $commentId) {
                $commentId = (int) $commentId;
                if ($commentId <= 0) {
                    continue;
                }
                $duplicateIds[] = $commentId;
                $affectedPosts[(int) $group['post_id']] = \true;
            }
        }
        $duplicateIds = \array_values(\array_unique($duplicateIds));
        if (empty($duplicateIds)) {
            return ['status' => 'success', 'message' => \__('No duplicate reviews found.', 'reviewx'), 'removed_reviews' => 0, 'processed_groups' => \count($groups), 'removed_comment_ids' => [], 'saas_synced' => \true];
        }
        $removedIds = \ReviewX\Services\ReviewService::withDeletedCommentSyncSuspended(function () use($duplicateIds) {
            return $this->deleteReviewsInWp($duplicateIds);
        });
        foreach (\array_keys($affectedPosts) as $postId) {
            $this->cacheServices->removeProductCache((int) $postId);
        }
        $this->cacheServices->removeCache();
        \delete_transient('rvx_admin_aggregation');
        $saasSynced = \true;
        $saasStatusCode = null;
        $saasError = null;
        if (!empty($removedIds) && Client::getUid()) {
            try {
                $response = $this->reviewsApi->reviewBulkSoftDelete(['wp_id' => $removedIds]);
                $saasStatusCode = $response->getStatusCode();
                $saasSynced = $saasStatusCode >= 200 && $saasStatusCode < 300;
                if (!$saasSynced) {
                    $responseBody = $response->autoParse();
                    $saasError = (string) ($responseBody['message'] ?? \__('ReviewX Cloud bulk delete failed.', 'reviewx'));
                    Helper::rvxLog(['event' => 'duplicate_review_saas_delete_failed', 'status_code' => $saasStatusCode, 'removed_comment_ids' => $removedIds, 'response' => $responseBody], 'warning');
                }
            } catch (\Throwable $throwable) {
                $saasSynced = \false;
                $saasError = $throwable->getMessage();
                Helper::rvxLog(['event' => 'duplicate_review_saas_delete_exception', 'removed_comment_ids' => $removedIds, 'error' => $throwable->getMessage()], 'error');
            }
        } elseif (!empty($removedIds)) {
            $saasSynced = \false;
            $saasError = \__('Missing site UID for ReviewX Cloud sync.', 'reviewx');
            Helper::rvxLog(['event' => 'duplicate_review_saas_delete_skipped', 'removed_comment_ids' => $removedIds, 'reason' => 'missing_site_uid'], 'warning');
        }
        $this->clearDuplicateReviewScanCache();
        $message = $saasSynced ? \sprintf(
            /* translators: %d: removed duplicate review count */
            \__('Removed %d duplicate reviews from WordPress and ReviewX Cloud.', 'reviewx'),
            \count($removedIds)
        ) : \sprintf(
            /* translators: %d: removed duplicate review count */
            \__('Removed %d duplicate reviews from WordPress. ReviewX Cloud sync needs retry.', 'reviewx'),
            \count($removedIds)
        );
        return ['status' => $saasSynced ? 'success' : 'warning', 'message' => $message, 'removed_reviews' => \count($removedIds), 'processed_groups' => \count($groups), 'removed_comment_ids' => $removedIds, 'saas_synced' => $saasSynced, 'saas_status_code' => $saasStatusCode, 'saas_error' => $saasError];
    }
    public function clearDuplicateReviewScanCache() : void
    {
        \delete_transient(self::SCAN_CACHE_KEY);
    }
    private function buildDuplicateReviewGroups() : array
    {
        global $wpdb;
        $postTypes = $this->getManagedPostTypes();
        if (empty($postTypes)) {
            return $this->emptyReport(\true);
        }
        $placeholders = \implode(', ', \array_fill(0, \count($postTypes), '%s'));
        $commentsTable = $wpdb->comments;
        $postsTable = $wpdb->posts;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table names come from $wpdb and the post type values are safely bound through prepare().
        $sql = $wpdb->prepare("SELECT c.comment_ID,\n                    c.comment_post_ID,\n                    c.comment_author,\n                    c.comment_author_email,\n                    c.comment_content,\n                    c.comment_date,\n                    c.comment_date_gmt,\n                    p.post_title,\n                    p.post_type\n             FROM {$commentsTable} c\n             INNER JOIN {$postsTable} p ON p.ID = c.comment_post_ID\n             WHERE c.comment_parent = 0\n               AND c.comment_approved != 'trash'\n               AND p.post_type IN ({$placeholders})\n               AND p.post_status NOT IN ('trash', 'auto-draft')\n             ORDER BY c.comment_post_ID ASC, c.comment_date_gmt ASC, c.comment_ID ASC", $postTypes);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-triggered duplicate scan over managed review posts.
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $groupedReviews = [];
        foreach ($rows as $row) {
            $identity = $this->normalizeIdentity((string) ($row['comment_author'] ?? ''), (string) ($row['comment_author_email'] ?? ''));
            $content = $this->normalizeCommentContent((string) ($row['comment_content'] ?? ''));
            if ($identity === '') {
                continue;
            }
            $groupKey = $this->buildIdentityBucketKey((int) $row['comment_post_ID'], $identity);
            $normalizedRow = ['comment_ID' => (int) $row['comment_ID'], 'comment_date' => (string) ($row['comment_date'] ?? ''), 'comment_date_gmt' => (string) ($row['comment_date_gmt'] ?? ''), 'comment_content' => (string) ($row['comment_content'] ?? ''), 'normalized_content' => $content];
            if (!isset($groupedReviews[$groupKey])) {
                $groupedReviews[$groupKey] = ['post_id' => (int) $row['comment_post_ID'], 'post_title' => (string) ($row['post_title'] ?? '' ?: \__('(no title)', 'reviewx')), 'post_type' => (string) ($row['post_type'] ?? ''), 'identity' => $identity, 'author' => (string) ($row['comment_author'] ?? ''), 'author_email' => (string) ($row['comment_author_email'] ?? ''), 'rows' => [], 'content_groups' => [], 'empty_rows' => []];
            }
            $groupedReviews[$groupKey]['rows'][] = $normalizedRow;
            if ($content === '') {
                $groupedReviews[$groupKey]['empty_rows'][] = $normalizedRow;
                continue;
            }
            if (!isset($groupedReviews[$groupKey]['content_groups'][$content])) {
                $groupedReviews[$groupKey]['content_groups'][$content] = [];
            }
            $groupedReviews[$groupKey]['content_groups'][$content][] = $normalizedRow;
        }
        $groups = [];
        $postsAffected = [];
        $duplicateReviews = 0;
        foreach ($groupedReviews as $group) {
            foreach ($group['content_groups'] as $contentKey => $contentRows) {
                if (\count($contentRows) < 2) {
                    continue;
                }
                $this->appendDuplicateGroup($groups, $postsAffected, $duplicateReviews, $group, $contentRows[0], \array_slice($contentRows, 1), 'content:' . $contentKey);
            }
            if (empty($group['empty_rows'])) {
                continue;
            }
            $anchorRow = $this->findFirstRowWithContent($group['rows']);
            $duplicateRows = [];
            if ($anchorRow !== null) {
                $duplicateRows = $group['empty_rows'];
            } elseif (\count($group['empty_rows']) > 1) {
                $anchorRow = $group['empty_rows'][0];
                $duplicateRows = \array_slice($group['empty_rows'], 1);
            }
            if ($anchorRow === null || empty($duplicateRows)) {
                continue;
            }
            $this->appendDuplicateGroup($groups, $postsAffected, $duplicateReviews, $group, $anchorRow, $duplicateRows, 'empty-feedback');
        }
        \usort($groups, static function (array $first, array $second) {
            if ($first['duplicates_to_remove'] === $second['duplicates_to_remove']) {
                return \strcmp((string) $second['latest_comment_date'], (string) $first['latest_comment_date']);
            }
            return $second['duplicates_to_remove'] <=> $first['duplicates_to_remove'];
        });
        return ['has_scan' => \true, 'scanned_at' => \wp_date('Y-m-d H:i:s'), 'duplicate_groups' => $groups, 'stats' => ['groups' => \count($groups), 'duplicate_reviews' => $duplicateReviews, 'posts_affected' => \count($postsAffected)]];
    }
    private function deleteReviewsInWp(array $commentIds) : array
    {
        $removedIds = [];
        foreach ($commentIds as $commentId) {
            $commentId = (int) $commentId;
            if ($commentId <= 0) {
                continue;
            }
            $comment = \get_comment($commentId);
            if (!$comment) {
                $removedIds[] = $commentId;
                continue;
            }
            if ((new \ReviewX\Services\ReviewService())->deleteCommentTreeInWp($commentId)) {
                $removedIds[] = $commentId;
            }
        }
        return $removedIds;
    }
    private function getManagedPostTypes() : array
    {
        $postTypes = \array_values((new CptHelper())->usedCPTOnSync('used'));
        if (empty($postTypes)) {
            return ['product'];
        }
        if (!\in_array('product', $postTypes, \true)) {
            $postTypes[] = 'product';
        }
        return \array_values(\array_unique(\array_filter($postTypes, 'is_string')));
    }
    private function normalizeIdentity(string $author, string $authorEmail) : string
    {
        $normalizedAuthor = $this->normalizeComparableText($author);
        $normalizedEmail = $this->normalizeEmail($authorEmail);
        if ($normalizedAuthor === '' && $normalizedEmail === '') {
            return '';
        }
        return $normalizedAuthor . '|' . $normalizedEmail;
    }
    private function normalizeEmail(string $email) : string
    {
        return \strtolower(\trim($email));
    }
    private function normalizeCommentContent(string $commentContent) : string
    {
        $content = \html_entity_decode($commentContent, \ENT_QUOTES, 'UTF-8');
        $content = \wp_strip_all_tags($content);
        $content = \strtolower($content);
        $content = \preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', $content);
        if (!\is_string($content)) {
            return '';
        }
        return \trim(\preg_replace('/\\s+/u', ' ', $content) ?? '');
    }
    private function normalizeComparableText(string $value) : string
    {
        $value = \html_entity_decode($value, \ENT_QUOTES, 'UTF-8');
        $value = \wp_strip_all_tags($value);
        $value = \strtolower($value);
        $value = \preg_replace('/\\s+/u', ' ', $value);
        if (!\is_string($value)) {
            return '';
        }
        return \trim($value);
    }
    private function shouldTreatIncomingReviewAsDuplicate(string $incomingContent, string $candidateContent) : bool
    {
        if ($incomingContent === '') {
            return \true;
        }
        return $candidateContent === $incomingContent;
    }
    private function findFirstRowWithContent(array $rows) : ?array
    {
        foreach ($rows as $row) {
            if (!empty($row['normalized_content'])) {
                return $row;
            }
        }
        return null;
    }
    private function appendDuplicateGroup(array &$groups, array &$postsAffected, int &$duplicateReviews, array $group, array $anchorRow, array $duplicateRows, string $signature) : void
    {
        if (empty($duplicateRows)) {
            return;
        }
        $allRows = $this->sortGroupRows(\array_merge([$anchorRow], $duplicateRows));
        $duplicateIds = \array_values(\array_map('intval', \array_column($duplicateRows, 'comment_ID')));
        $postsAffected[(int) $group['post_id']] = \true;
        $duplicateReviews += \count($duplicateIds);
        $groups[] = ['group_key' => $this->buildGroupKey((int) $group['post_id'], (string) $group['identity'], $signature), 'post_id' => (int) $group['post_id'], 'post_title' => (string) $group['post_title'], 'post_type' => (string) $group['post_type'], 'author' => (string) $group['author'], 'author_email' => (string) $group['author_email'], 'content_preview' => $this->buildContentPreview($anchorRow, $duplicateRows), 'keep_comment_id' => (int) $anchorRow['comment_ID'], 'all_comment_ids' => \array_values(\array_map('intval', \array_column($allRows, 'comment_ID'))), 'duplicate_comment_ids' => $duplicateIds, 'total_reviews' => \count($allRows), 'duplicates_to_remove' => \count($duplicateIds), 'oldest_comment_date' => (string) ($allRows[0]['comment_date'] ?? ''), 'latest_comment_date' => (string) ($allRows[\count($allRows) - 1]['comment_date'] ?? '')];
    }
    private function sortGroupRows(array $rows) : array
    {
        \usort($rows, static function (array $first, array $second) : int {
            $firstDate = (string) ($first['comment_date_gmt'] ?? '');
            $secondDate = (string) ($second['comment_date_gmt'] ?? '');
            if ($firstDate === $secondDate) {
                return (int) ($first['comment_ID'] ?? 0) <=> (int) ($second['comment_ID'] ?? 0);
            }
            return \strcmp($firstDate, $secondDate);
        });
        return $rows;
    }
    private function buildContentPreview(array $anchorRow, array $duplicateRows) : string
    {
        $rows = \array_merge([$anchorRow], $duplicateRows);
        foreach ($rows as $row) {
            $content = (string) ($row['comment_content'] ?? '');
            if ($this->normalizeCommentContent($content) !== '') {
                return \wp_html_excerpt($content, 120, '...');
            }
        }
        return \__('(empty review feedback)', 'reviewx');
    }
    private function emptyReport(bool $hasScan) : array
    {
        return ['has_scan' => $hasScan, 'scanned_at' => null, 'duplicate_groups' => [], 'stats' => ['groups' => 0, 'duplicate_reviews' => 0, 'posts_affected' => 0]];
    }
    private function buildIdentityBucketKey(int $postId, string $identity) : string
    {
        return \sha1($postId . '|' . $identity);
    }
    private function buildGroupKey(int $postId, string $identity, string $signature) : string
    {
        return \sha1($postId . '|' . $identity . '|' . $signature);
    }
}
