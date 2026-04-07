<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Api\ReviewImportAndExportApi;
use ReviewX\CPT\CptHelper;
use ReviewX\Services\Api\LoginService;
use ReviewX\Api\AuthApi;
use ReviewX\Utilities\Helper;
use ReviewX\Utilities\Auth\Client;
use Exception;
class ImportExportServices extends \ReviewX\Services\Service
{
    private \ReviewX\Services\DataSyncService $dataSyncService;
    private \ReviewX\Services\CacheServices $cacheServices;
    private LoginService $loginService;
    public function __construct()
    {
        $this->dataSyncService = new \ReviewX\Services\DataSyncService();
        $this->cacheServices = new \ReviewX\Services\CacheServices();
        $this->loginService = new LoginService();
    }
    public function importSupportedAppStore($data)
    {
        return (new ReviewImportAndExportApi())->importSupportedAppStore($data);
    }
    public function importStore($request)
    {
        $files = $request->get_file_params();
        $data = $request->get_params();
        // Direct WP DB import
        $response = $this->importReviewStore($files, $data);
        global $wpdb;
        $rvxSites = esc_sql($wpdb->prefix . 'rvx_sites');
        // Initialize tables and reset sync flag
        (new \ReviewX\Handlers\ReviewXInit\LoadReviewxCreateSiteTable())->init();
        \set_transient('rvx_reset_sync_flag', \true, 300);
        // 5 mins TTL
        $cache_key = 'rvx_site_uid';
        $uid = \wp_cache_get($cache_key, 'reviewx');
        if (\false === $uid) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table read
            $uid = $wpdb->get_var('SELECT uid FROM `' . $rvxSites . '` ORDER BY id DESC LIMIT 1');
            if ($uid) {
                \wp_cache_set($cache_key, $uid, 'reviewx', 86400);
                // 1 day
            }
        }
        if ($uid) {
            // Mark as not synced initially
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table update, no standard WP API available
            $wpdb->update($rvxSites, ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
            // Start initial sync for all enabled post types
            $enabled_post_types = (new CptHelper())->usedCPTOnSync('used');
            foreach ($enabled_post_types as $post_type) {
                $this->dataSyncService->dataSync('default', $post_type);
            }
        }
        // Always clean cache and redirect, even if UID or API failed
        $this->cacheServices->removeCache();
        $this->loginService->resetPostMeta();
        // Invalidate aggregation transient so next /reviews call fetches fresh data
        \delete_transient('rvx_admin_aggregation');
        return ['status' => 'success', 'message' => 'WordPress import success! Initiating synchronization with ReviewX Cloud...', 'data' => $response];
    }
    public function importReviewStore($files, $data)
    {
        // Prevent timeout for large files
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for large CSV import processing
        \set_time_limit(0);
        $request = $data;
        $totalProcessed = 0;
        $successCount = 0;
        $file_path = $files['file']['tmp_name'];
        $wpReviewIds = [];
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        $content = $wp_filesystem->get_contents($file_path);
        if (empty($content)) {
            return ['total' => 0, 'success' => 0, 'failed' => 0];
        }
        $lines = \explode(\PHP_EOL, $content);
        $header_line = \array_shift($lines);
        if (empty($header_line)) {
            return ['total' => 0, 'success' => 0, 'failed' => 0];
        }
        $header = \str_getcsv($header_line);
        $map = $request['map'] ?? [];
        $productIdColumn = $map['product_id'] ?? null;
        $chunkSize = 50;
        $currentChunk = [];
        foreach ($lines as $line) {
            if (empty(\trim($line))) {
                continue;
            }
            $row = \str_getcsv($line);
            if (\count($header) === \count($row)) {
                $reviewData = \array_combine($header, $row);
                if ($productIdColumn && !empty($reviewData[$productIdColumn])) {
                    $currentChunk[] = $reviewData;
                }
            }
            if (\count($currentChunk) >= $chunkSize) {
                $results = $this->processReviewBatch($currentChunk, $request);
                $totalProcessed += $results['total'];
                $successCount += $results['success'];
                $wpReviewIds = \array_merge($wpReviewIds, $results['ids']);
                $currentChunk = [];
            }
        }
        // Process remaining
        if (!empty($currentChunk)) {
            $results = $this->processReviewBatch($currentChunk, $request);
            $totalProcessed += $results['total'];
            $successCount += $results['success'];
            $wpReviewIds = \array_merge($wpReviewIds, $results['ids']);
        }
        $response = ['total' => $totalProcessed, 'success' => $successCount, 'failed' => $totalProcessed - $successCount];
        // Log history to SaaS
        try {
            $historyPayload = ['uid' => Client::getUid(), 'name' => \basename($files['file']['name']), 'map' => $request['map'] ?? [], 'wp_review_ids' => $wpReviewIds, 'stats' => ['total_reviews' => $totalProcessed, 'success_reviews' => $successCount, 'failed_reviews' => $totalProcessed - $successCount]];
            Helper::rvxLog('Logging import history to SaaS... Payload: ' . \json_encode($historyPayload), 'debug');
            $logResponse = (new ReviewImportAndExportApi())->logImportHistory($historyPayload);
            Helper::rvxLog('SaaS logImportHistory response status: ' . $logResponse->getStatusCode() . ' Body: ' . $logResponse->getBody(), 'debug');
        } catch (\Throwable $e) {
            Helper::rvxLog('SaaS logImportHistory failed: ' . $e->getMessage(), 'error');
        }
        return $response;
    }
    private function processReviewBatch(array $reviews, array $request) : array
    {
        $ids = [];
        $map = $request['map'] ?? [];
        $productIdColumn = $map['product_id'] ?? null;
        $total = 0;
        $success = 0;
        foreach ($reviews as $reviewData) {
            $total++;
            $wpProductId = $productIdColumn && isset($reviewData[$productIdColumn]) ? (int) $reviewData[$productIdColumn] : 0;
            if (!$wpProductId) {
                Helper::rvxLog('Missing product ID in row: ' . \json_encode($reviewData), 'warning');
                continue;
            }
            $postType = $reviewData['Post_Type'] ?? 'product';
            try {
                // We can optimize transient deletion to happen once per product per batch if needed,
                // but for now keeping it safe.
                \delete_transient("rvx_{$wpProductId}_latest_reviews");
                \delete_transient("rvx_{$wpProductId}_latest_reviews_insight");
                $commentId = $this->insertReview($wpProductId, $reviewData, $request, $postType);
                if ($commentId) {
                    $ids[] = $commentId;
                    $success++;
                }
            } catch (\Throwable $e) {
                // Insert failed, but continue with the next review
                Helper::rvxLog('Review insertion failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 'error');
            }
        }
        return ['total' => $total, 'success' => $success, 'ids' => $ids];
    }
    public function insertReview($reviews_id, $review_data, $request, $post_type)
    {
        $mediaArray = [];
        $map = $request['map'] ?? [];
        $attachmentKey = $map['attachment'] ?? null;
        if ($attachmentKey && isset($review_data[$attachmentKey]) && !empty($review_data[$attachmentKey])) {
            $mediaArray = \explode(',', $review_data[$attachmentKey]);
        }
        $comment_type = 'review';
        if (!empty($post_type) && \strtolower($post_type) != 'product') {
            $comment_type = 'comment';
        }
        $customerNameKey = $request['map']['customer_name'] ?? null;
        $customerEmailKey = $request['map']['customer_email'] ?? null;
        $feedbackKey = $request['map']['feedback'] ?? null;
        $createdAtKey = $request['map']['created_at'] ?? null;
        $comment_data = ['comment_post_ID' => $reviews_id, 'comment_author' => $customerNameKey && isset($review_data[$customerNameKey]) ? $review_data[$customerNameKey] : 'Anonymous', 'comment_author_email' => $customerEmailKey && isset($review_data[$customerEmailKey]) ? $review_data[$customerEmailKey] : '', 'comment_content' => $feedbackKey && isset($review_data[$feedbackKey]) ? $review_data[$feedbackKey] : '', 'comment_date' => $createdAtKey && !empty($review_data[$createdAtKey]) && \strtotime($review_data[$createdAtKey]) !== \false ? \wp_date('Y-m-d H:i:s', \strtotime($review_data[$createdAtKey])) : \wp_date('Y-m-d H:i:s'), 'comment_approved' => Helper::arrayGet($request, 'status'), 'comment_type' => $comment_type];
        $comment_id = \wp_insert_comment($comment_data);
        if ($comment_id && !\is_wp_error($comment_id)) {
            $titleKey = $request['map']['review_title'] ?? null;
            $titleValue = $titleKey && isset($review_data[$titleKey]) ? $review_data[$titleKey] : null;
            \update_comment_meta($comment_id, 'reviewx_title', $titleValue);
            $ratingColumn = $request['map']['rating'] ?? null;
            $rating = $ratingColumn && isset($review_data[$ratingColumn]) ? (int) $review_data[$ratingColumn] : 5;
            if ($rating > 5) {
                $rating = 5;
            } elseif ($rating < 1) {
                $rating = 1;
            }
            \update_comment_meta($comment_id, 'rating', $rating);
            $processedMedia = [];
            if (!empty($mediaArray)) {
                Helper::rvxLog('Processing attachments for review ' . $comment_id . ': ' . \implode(', ', $mediaArray), 'debug');
                foreach ($mediaArray as $url) {
                    $processedMedia[] = $this->sideloadAttachment(\trim($url));
                }
            }
            \update_comment_meta($comment_id, 'reviewx_attachments', $processedMedia);
            \update_comment_meta($comment_id, 'verified', Helper::arrayGet($request, 'verified'));
            \update_comment_meta($comment_id, 'rvx_review_version', 'v2');
            // Explicitly trigger aggregation if approved
            if ($comment_data['comment_approved'] == 1) {
                \ReviewX\CPT\CptAverageRating::update_average_rating($reviews_id);
            }
            // Handle Review Reply
            $replyContentColumn = $request['map']['review_reply'] ?? null;
            if ($replyContentColumn && isset($review_data[$replyContentColumn]) && !empty($review_data[$replyContentColumn]) && $comment_id) {
                $repliedAtColumn = $request['map']['replied_at'] ?? null;
                $repliedAtTime = \time();
                if ($repliedAtColumn && !empty($review_data[$repliedAtColumn])) {
                    $parsed = \strtotime($review_data[$repliedAtColumn]);
                    if ($parsed !== \false) {
                        $repliedAtTime = $parsed;
                    }
                }
                $currentUser = Helper::getWpCurrentUser();
                $replyData = ['comment_post_ID' => $reviews_id, 'comment_author' => $currentUser ? $currentUser->display_name : 'Shop Owner', 'comment_author_email' => $currentUser ? $currentUser->user_email : \get_option('admin_email'), 'comment_content' => $review_data[$replyContentColumn], 'comment_type' => 'comment', 'comment_parent' => $comment_id, 'comment_approved' => 1, 'comment_date' => \wp_date('Y-m-d H:i:s', $repliedAtTime)];
                \wp_insert_comment($replyData);
            }
        }
        return $comment_id;
    }
    /**
     * @throws Exception
     */
    public function importRollback($data)
    {
        return (new ReviewImportAndExportApi())->importRollback($data);
    }
    public function rollbackImportByIds($data)
    {
        $wpReviewIds = $data['wp_review_ids'] ?? [];
        if (empty($wpReviewIds)) {
            return ['status' => 'error', 'message' => 'Missing wp_review_ids'];
        }
        $count = 0;
        $affectedPosts = [];
        foreach ($wpReviewIds as $commentId) {
            $comment = \get_comment($commentId);
            if ($comment) {
                $affectedPosts[] = $comment->comment_post_ID;
                // Find and delete replies (child comments)
                $replies = \get_comments(['parent' => $commentId]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \wp_delete_comment($reply->comment_ID, \true);
                    }
                }
                if (\wp_delete_comment($commentId, \true)) {
                    $count++;
                }
            }
        }
        // Clear caches for affected products
        foreach (\array_unique($affectedPosts) as $postId) {
            (new \ReviewX\Services\CacheServices())->removeProductCache($postId);
            \ReviewX\CPT\CptAverageRating::update_average_rating($postId);
        }
        (new \ReviewX\Services\CacheServices())->removeCache();
        return ['status' => 'success', 'message' => "Successfully deleted {$count} reviews", 'deleted_count' => $count];
    }
    /**
     * @throws Exception
     */
    public function importRestore($data)
    {
        return (new ReviewImportAndExportApi())->importRestore($data);
    }
    public function exportCsv($data)
    {
        return (new ReviewImportAndExportApi())->exportCsv($data);
    }
    public function exportHistory()
    {
        return (new ReviewImportAndExportApi())->exportHistory();
    }
    public function importHistory()
    {
        return (new ReviewImportAndExportApi())->importHistory();
    }
    /**
     * Sideload an attachment from a URL to the local media library.
     * 
     * @param string $url
     * @return string
     */
    protected function sideloadAttachment($url)
    {
        if (empty($url) || !\filter_var($url, \FILTER_VALIDATE_URL)) {
            Helper::rvxLog('Invalid URL for sideload: ' . $url, 'warning');
            return $url;
        }
        // 1. Same-Domain Check
        $homeUrl = \home_url();
        $parsedHome = \wp_parse_url($homeUrl);
        $parsedUrl = \wp_parse_url($url);
        if (isset($parsedUrl['host'], $parsedHome['host']) && $parsedUrl['host'] === $parsedHome['host']) {
            return $url;
        }
        // 2. Duplicate Prevention (check by source URL)
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Intentional deduplication by source URL before sideload
            'meta_query' => [['key' => '_rvx_source_url', 'value' => $url]],
            'posts_per_page' => 1,
            'fields' => 'ids',
        ];
        $existing = \ReviewX\get_posts($args);
        if (!empty($existing)) {
            return \wp_get_attachment_url($existing[0]);
        }
        // 3. Sideloading Process
        if (!\function_exists('download_url')) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!\function_exists('ReviewX\\media_handle_sideload')) {
            require_once \ABSPATH . 'wp-admin/includes/media.php';
            require_once \ABSPATH . 'wp-admin/includes/image.php';
        }
        // Handle protocol-relative URLs
        if (\strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        // Skip sideloading for external video links (YouTube, Vimeo, etc.)
        $videoHosts = ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com'];
        $host = \wp_parse_url($url, \PHP_URL_HOST);
        foreach ($videoHosts as $videoHost) {
            if ($host && \strpos($host, $videoHost) !== \false) {
                Helper::rvxLog('Skipping sideload for remote video URL: ' . $url, 'debug');
                return $url;
            }
        }
        // Strip query string for filename extraction to ensure WP gets the correct extension
        $clean_url = \strtok($url, '?');
        $file_name = \basename($clean_url);
        $tmp = \download_url($url);
        if (\is_wp_error($tmp)) {
            Helper::rvxLog('Download failed for: ' . $url . ' Error: ' . $tmp->get_error_message(), 'error');
            return $url;
        }
        $file_array = ['name' => $file_name, 'tmp_name' => $tmp];
        // Sideload keeping the file
        $id = \ReviewX\media_handle_sideload($file_array, 0);
        if (\is_wp_error($id)) {
            Helper::rvxLog('media_handle_sideload failed for: ' . $url . ' Error: ' . $id->get_error_message(), 'error');
            global $wp_filesystem;
            if (!empty($wp_filesystem)) {
                $wp_filesystem->delete($tmp);
            } else {
                \wp_delete_file($tmp);
            }
            return $url;
        }
        // Store original source URL as metadata
        \update_post_meta($id, '_rvx_source_url', $url);
        return \wp_get_attachment_url($id);
    }
}
