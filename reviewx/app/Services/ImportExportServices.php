<?php

namespace Rvx\Services;

use Rvx\Api\ReviewImportAndExportApi;
use Rvx\CPT\CptHelper;
use Rvx\Services\Api\LoginService;
use Rvx\Api\AuthApi;
use Rvx\Utilities\Helper;
use Exception;
class ImportExportServices extends \Rvx\Services\Service
{
    private \Rvx\Services\DataSyncService $dataSyncService;
    private \Rvx\Services\CacheServices $cacheServices;
    private LoginService $loginService;
    public function __construct()
    {
        $this->dataSyncService = new \Rvx\Services\DataSyncService();
        $this->cacheServices = new \Rvx\Services\CacheServices();
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
        // Initialize tables and reset sync flag
        (new \Rvx\Handlers\RvxInit\LoadReviewxCreateSiteTable())->init();
        \set_transient('rvx_reset_sync_flag', \true, 300);
        // 5 mins TTL
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            // Mark as not synced initially
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
        \set_time_limit(0);
        $request = $data;
        $reviews = [];
        $totalProcessed = 0;
        $successCount = 0;
        $file = $files['file']['tmp_name'];
        $wpReviewIds = [];
        if (($handle = \fopen($file, 'r')) !== \FALSE) {
            // Get the header row
            $header = \fgetcsv($handle);
            if (!$header) {
                \fclose($handle);
                return ['total' => 0, 'success' => 0, 'failed' => 0];
            }
            $map = $request['map'] ?? [];
            $productIdColumn = $map['product_id'] ?? null;
            $chunkSize = 50;
            $currentChunk = [];
            while (($row = \fgetcsv($handle)) !== \FALSE) {
                // Combine header with row data
                if (\count($header) === \count($row)) {
                    $reviewData = \array_combine($header, $row);
                    // Basic validation
                    if ($productIdColumn && !empty($reviewData[$productIdColumn])) {
                        $currentChunk[] = $reviewData;
                    }
                }
                // Process chunk
                if (\count($currentChunk) >= $chunkSize) {
                    $results = $this->processReviewBatch($currentChunk, $request);
                    $totalProcessed += $results['total'];
                    $successCount += $results['success'];
                    $wpReviewIds = \array_merge($wpReviewIds, $results['ids']);
                    $currentChunk = [];
                    // Optional: distinct cleanups if needed per chunk
                }
            }
            // Process remaining
            if (!empty($currentChunk)) {
                $results = $this->processReviewBatch($currentChunk, $request);
                $totalProcessed += $results['total'];
                $successCount += $results['success'];
                $wpReviewIds = \array_merge($wpReviewIds, $results['ids']);
            }
            \fclose($handle);
        }
        $response = ['total' => $totalProcessed, 'success' => $successCount, 'failed' => $totalProcessed - $successCount];
        // Log history to SaaS
        try {
            (new \Rvx\Api\ReviewImportAndExportApi())->logImportHistory(['name' => \basename($files['file']['name']), 'map' => $request['map'] ?? [], 'wp_review_ids' => $wpReviewIds, 'stats' => ['total_reviews' => $totalProcessed, 'success_reviews' => $successCount, 'failed_reviews' => $totalProcessed - $successCount]]);
        } catch (\Exception $e) {
            \error_log("Failed to log import history to SaaS: " . $e->getMessage());
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
            } catch (Exception $e) {
                \error_log("Failed to insert review: " . $e->getMessage());
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
            foreach ($mediaArray as $url) {
                $processedMedia[] = $this->sideloadAttachment(\trim($url));
            }
            \update_comment_meta($comment_id, 'reviewx_attachments', $processedMedia);
            \update_comment_meta($comment_id, 'verified', Helper::arrayGet($request, 'verified'));
            \update_comment_meta($comment_id, 'rvx_review_version', 'v2');
            // Explicitly trigger aggregation if approved
            if ($comment_data['comment_approved'] == 1) {
                \Rvx\CPT\CptAverageRating::update_average_rating($reviews_id);
            }
            // Handle Review Reply
            $replyContentColumn = $request['map']['review_reply'] ?? null;
            if ($replyContentColumn && isset($review_data[$replyContentColumn]) && !empty($review_data[$replyContentColumn]) && $comment_id) {
                $repliedAtColumn = $request['map']['replied_at'] ?? null;
                $repliedAt = $repliedAtColumn && isset($review_data[$repliedAtColumn]) && !empty($review_data[$repliedAtColumn]) ? \strtotime($review_data[$repliedAtColumn]) : \time();
                $currentUser = Helper::getWpCurrentUser();
                $replyData = ['comment_post_ID' => $reviews_id, 'comment_author' => $currentUser ? $currentUser->display_name : 'Shop Owner', 'comment_author_email' => $currentUser ? $currentUser->user_email : \get_option('admin_email'), 'comment_content' => $review_data[$replyContentColumn], 'comment_type' => 'comment', 'comment_parent' => $comment_id, 'comment_approved' => 1, 'comment_date' => \wp_date('Y-m-d H:i:s', $repliedAt)];
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
            $comment = \Rvx\get_comment($commentId);
            if ($comment) {
                $affectedPosts[] = $comment->comment_post_ID;
                // Find and delete replies (child comments)
                $replies = \Rvx\get_comments(['parent' => $commentId]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \Rvx\wp_delete_comment($reply->comment_ID, \true);
                    }
                }
                if (\Rvx\wp_delete_comment($commentId, \true)) {
                    $count++;
                }
            }
        }
        // Clear caches for affected products
        foreach (\array_unique($affectedPosts) as $postId) {
            (new \Rvx\Services\CacheServices())->removeProductCache($postId);
            \Rvx\CPT\CptAverageRating::update_average_rating($postId);
        }
        (new \Rvx\Services\CacheServices())->removeCache();
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
            return $url;
        }
        // 1. Same-Domain Check
        $homeUrl = \Rvx\home_url();
        $parsedHome = \parse_url($homeUrl);
        $parsedUrl = \parse_url($url);
        if (isset($parsedUrl['host'], $parsedHome['host']) && $parsedUrl['host'] === $parsedHome['host']) {
            return $url;
        }
        // 2. Duplicate Prevention (check by source URL)
        $args = ['post_type' => 'attachment', 'post_status' => 'inherit', 'meta_query' => [['key' => '_rvx_source_url', 'value' => $url]], 'posts_per_page' => 1, 'fields' => 'ids'];
        $existing = \Rvx\get_posts($args);
        if (!empty($existing)) {
            return \Rvx\wp_get_attachment_url($existing[0]);
        }
        // 3. Sideloading Process
        if (!\function_exists('Rvx\\download_url')) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!\function_exists('Rvx\\media_handle_sideload')) {
            require_once \ABSPATH . 'wp-admin/includes/media.php';
            require_once \ABSPATH . 'wp-admin/includes/image.php';
        }
        $tmp = \Rvx\download_url($url);
        if (\is_wp_error($tmp)) {
            \error_log("RVX Import: Failed to download attachment ({$url}): " . $tmp->get_error_message());
            return $url;
        }
        $file_array = ['name' => \basename($url), 'tmp_name' => $tmp];
        // Sideload keeping the file
        $id = \Rvx\media_handle_sideload($file_array, 0);
        if (\is_wp_error($id)) {
            @\unlink($tmp);
            \error_log("RVX Import: Failed to sideload attachment ({$url}): " . $id->get_error_message());
            return $url;
        }
        // Store original source URL as metadata
        \update_post_meta($id, '_rvx_source_url', $url);
        return \Rvx\wp_get_attachment_url($id);
    }
}
