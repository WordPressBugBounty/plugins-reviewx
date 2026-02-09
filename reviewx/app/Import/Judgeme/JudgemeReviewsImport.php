<?php

namespace Rvx\Import\Judgeme;

use Rvx\Api\AuthApi;
use Rvx\Services\CacheServices;
use Rvx\Services\DataSyncService;
use Rvx\Utilities\Helper;
use Generator;
use Throwable;
class JudgemeReviewsImport
{
    private DataSyncService $dataSyncService;
    protected CacheServices $cacheServices;
    protected string $judgemeDomain;
    protected string $judgemeToken;
    protected string $tmpDir;
    protected string $csvFilePath;
    protected bool $isSync = \true;
    protected int $transientTtl = HOUR_IN_SECONDS;
    public function __construct($isSync = \true)
    {
        $this->cacheServices = new CacheServices();
        $this->dataSyncService = new DataSyncService();
        $this->judgemeDomain = get_option('judgeme_domain');
        $this->judgemeToken = get_option('judgeme_shop_token');
        $this->isSync = $isSync;
        $this->transientTtl = 3 * HOUR_IN_SECONDS;
        // 3 hours
        // Use System uploads dir
        // $this->tmpDir = sys_get_temp_dir();
        // Use WordPress uploads dir
        $uploadDir = wp_upload_dir();
        $reviewxDir = $uploadDir['basedir'] . '/reviewx';
        if (!\file_exists($reviewxDir)) {
            wp_mkdir_p($reviewxDir);
            // creates recursively with correct perms
        }
        $this->tmpDir = $reviewxDir;
        $this->csvFilePath = $this->tmpDir . '/judgeme-export.csv';
    }
    public function judgemeStatusDetect($request) : array
    {
        if (!empty($this->judgemeDomain) && !empty($this->judgemeToken)) {
            return ['success' => \true, 'message' => 'Judgeme found'];
        }
        return ['success' => \false, 'message' => 'Judgeme not found!'];
    }
    public function judgemeCSVdownload($request) : array
    {
        return ['success' => \false, 'message' => 'Judgeme CSV download can\'t be automatted anymore! Please try uploading CSV manually.'];
        // $cookie_file = tempnam($this->tmpDir, 'judgeme_cookie_');
        // $csvPath = $this->getCsvFilePath();
        // if (file_exists($csvPath)) {
        //     if (file_exists($cookie_file)) {
        //         @unlink($cookie_file);
        //     }
        //     return [
        //         'success' => true,
        //         'message' => 'Judgeme Export CSV file already exists.',
        //     ];
        // }
        // if (empty($this->judgemeDomain) || empty($this->judgemeToken)) {
        //     return ['success' => false, 'message' => 'Judge.me credentials are missing.'];
        // }
        // $login_url = $this->generateJudgemeLoginURL();
        // $export_url = 'https://app.judge.me/reviews/export?export_mode=published&from_settings=true';
        // if (empty($login_url)) {
        //     return ['success' => false, 'message' => 'Login URL could not be generated.'];
        // }
        // try {
        //     // Step 1: Authenticate (gets and stores session cookies)
        //     $ch = curl_init($login_url);
        //     curl_setopt_array($ch, [
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_COOKIEJAR => $cookie_file,
        //         CURLOPT_COOKIEFILE => $cookie_file,
        //     ]);
        //     curl_exec($ch); // hits login URL, stores auth cookies in $cookie_file
        //     curl_close($ch);
        //     // Step 2: Download CSV using the same cookies
        //     $ch = curl_init($export_url);
        //     curl_setopt_array($ch, [
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_COOKIEJAR => $cookie_file,
        //         CURLOPT_COOKIEFILE => $cookie_file,
        //         CURLOPT_USERAGENT => 'Mozilla/5.0'
        //     ]);
        //     $csv_data = curl_exec($ch);
        //     $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //     $curl_error = curl_error($ch);
        //     curl_close($ch);
        //     if ($http_code !== 200 || empty($csv_data)) {
        //         return [
        //             'success' => false,
        //             'message' => 'Failed to download CSV.'
        //         ];
        //     }
        //     // Normalize line endings & strip UTF-8 BOM
        //     $csv_data = str_replace(["\r\n", "\r"], "\n", $csv_data);
        //     $csv_data = preg_replace('/^\xEF\xBB\xBF/', '', $csv_data);
        //     // Check if CSV has only header (no data rows)
        //     $lines = explode("\n", $csv_data);
        //     if (count($lines) <= 1) { // means only header or empty
        //         return [
        //             'success' => false,
        //             'message' => 'CSV file contains only headers and no reviews.'
        //         ];
        //     }
        //     // Directly save CSV to file
        //     if (file_put_contents($this->csvFilePath, $csv_data) === false) {
        //         return [
        //             'success' => false,
        //             'message' => 'Failed to save downloaded CSV file.'
        //         ];
        //     }
        //     return [
        //         'success' => true,
        //         'message' => 'Judgeme Export CSV file downloaded successfully.'
        //     ];
        // } finally {
        //     if (file_exists($cookie_file)) {
        //         @unlink($cookie_file);
        //     }
        // }
    }
    public function judgemeCSVUpload($request) : array
    {
        $csvPath = $this->getCsvFilePath();
        if (\file_exists($csvPath)) {
            return ['success' => \true, 'message' => 'Judgeme Export CSV file already exists.', 'csv_exists' => \true];
        }
        if (empty($this->judgemeDomain) || empty($this->judgemeToken)) {
            return ['success' => \false, 'message' => 'Judge.me credentials are missing.'];
        }
        $file = $_FILES['csv_file'];
        $filename = \basename($file['name']);
        if (\pathinfo($filename, \PATHINFO_EXTENSION) !== 'csv') {
            return ['success' => \false, 'message' => 'File must be a CSV file.'];
        }
        $tmp_name = $file['tmp_name'];
        $filetype = wp_check_filetype_and_ext($tmp_name, $filename);
        if (!\in_array($filetype['type'], ['text/csv', 'application/vnd.ms-excel'])) {
            return ['success' => \false, 'message' => 'Invalid file type. Must be a CSV file.'];
        }
        if (\move_uploaded_file($tmp_name, $this->csvFilePath)) {
            // Clear stale import progress so next chunk import recalculates totals
            \delete_transient('rvx_judgeme_total_count');
            \delete_transient('rvx_judgeme_imported_count');
            \delete_transient('rvx_judgeme_failed_count');
            // Reset completed flag just in case (so status API will show success=false)
            update_option('rvx_judgeme_import', \false);
            return ['success' => \true, 'message' => 'Judgeme Export CSV file uploaded successfully.', 'csv_exists' => \true];
        }
        return ['success' => \false, 'message' => 'Failed to save uploaded file.'];
    }
    public function judgemeImportChunk(array $params = []) : array
    {
        $chunk = isset($params['chunk']) ? (int) $params['chunk'] : 1;
        $csvPath = $this->getCsvFilePath();
        if (!\file_exists($csvPath)) {
            return ['success' => \false, 'message' => 'CSV file not found.', 'total_reviews' => 0, 'total_imported' => 0, 'total_failed' => 0];
        }
        // Reduce notifications during import
        remove_action('comment_post', 'wp_notify_postauthor');
        add_filter('comments_notify', '__return_false');
        // 1) Ensure we have the total count in transient
        $total = \get_transient('rvx_judgeme_total_count');
        if ($total === \false) {
            $total = 0;
            foreach ($this->streamJudgemeCSV($csvPath) as $_) {
                $total++;
            }
            set_transient('rvx_judgeme_total_count', $total, $this->transientTtl);
        }
        // If there are no reviews, short-circuit and set counters
        if ((int) $total === 0) {
            set_transient('rvx_judgeme_imported_count', 0, $this->transientTtl);
            set_transient('rvx_judgeme_failed_count', 0, $this->transientTtl);
            add_action('comment_post', 'wp_notify_postauthor');
            remove_filter('comments_notify', '__return_false');
            return ['success' => \true, 'message' => 'No reviews found in CSV.', 'total_reviews' => 0, 'total_imported' => 0, 'total_failed' => 0, 'import_finished' => \true];
        }
        // 2) Determine chunk size using the real total
        $chunkSize = $this->determineChunkSize((int) $total);
        $offset = ($chunk - 1) * $chunkSize;
        // 3) Load existing progress
        $importedCount = (int) \get_transient('rvx_judgeme_imported_count') ?: 0;
        $failedCount = (int) \get_transient('rvx_judgeme_failed_count') ?: 0;
        $currentIndex = 0;
        $processed = 0;
        // Stream and process only required chunk rows
        foreach ($this->streamJudgemeCSV($csvPath) as $row) {
            if ($currentIndex < $offset) {
                $currentIndex++;
                continue;
            }
            if ($processed >= $chunkSize) {
                break;
            }
            $result = $this->importSingleReviewWithStatus($row);
            if ($result['success']) {
                $importedCount++;
            } else {
                $failedCount++;
            }
            $processed++;
            $currentIndex++;
        }
        // Prevent overcounting (cap if exceeds total)
        if ($importedCount > $total) {
            $importedCount = $total;
            $failedCount = 0;
        }
        // Persist counts (3 hours)
        set_transient('rvx_judgeme_imported_count', $importedCount, $this->transientTtl);
        set_transient('rvx_judgeme_failed_count', $failedCount, $this->transientTtl);
        // Mark import completed if done
        if ($importedCount >= $total) {
            update_option('rvx_judgeme_import', \true);
        }
        set_transient('rvx_judgeme_failed_count', 0, $this->transientTtl);
        // update to `0`, even if few fails
        $this->cacheServices->removeCache();
        // Restore notifications
        add_action('comment_post', 'wp_notify_postauthor');
        remove_filter('comments_notify', '__return_false');
        $importFinished = $offset + $processed >= $total;
        return ['success' => \true, 'message' => 'Chunk import completed.', 'total_reviews' => (int) $total, 'total_imported' => $importedCount, 'total_failed' => $failedCount, 'import_finished' => $importFinished];
    }
    public function judgemeImportStatus($request) : array
    {
        $imported = (int) \get_transient('rvx_judgeme_imported_count') ?: 0;
        $total = (int) \get_transient('rvx_judgeme_total_count') ?: 0;
        $failed = (int) \get_transient('rvx_judgeme_failed_count') ?: 0;
        $completed = (bool) get_option('rvx_judgeme_import', \false);
        $login_url = $this->generateJudgemeLoginURL() ?: 'https://app.judge.me';
        // Cap imported to total to prevent overcount display
        $imported = \min($imported, $total);
        return ['success' => $completed, 'total_reviews' => $total, 'total_imported' => $imported, 'total_failed' => $failed, 'judgeme_login' => $login_url];
    }
    public function rvxReviewsSync($request)
    {
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            $wpdb->update($rvxSites, ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
            // Set the localStorage isAlreadySyncSuccess
            set_transient('rvx_reset_sync_flag', \true);
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
            $dataResponse = $this->dataSyncService->dataSync('default', 'product');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Judgeme Reviews sync fails', 500);
            }
            return Helper::rvxApi($dataResponse)->success('Judgeme Reviews sync successfully', 200);
        }
    }
    protected function generateJudgemeLoginURL() : string
    {
        if (empty($this->judgemeDomain) || empty($this->judgemeToken)) {
            return '';
        }
        $query = \http_build_query(['no_iframe' => 1, 'shop_domain' => $this->judgemeDomain, 'platform' => 'woocommerce']);
        $hmac = \hash_hmac('sha256', "no_iframe=1&platform=woocommerce&shop_domain={$this->judgemeDomain}", $this->judgemeToken, \false);
        return 'https://app.judge.me/home?' . $query . '&hmac=' . $hmac;
    }
    /**
     * Stream CSV rows lazily.
     * Cleans BOM from header keys if present.
     *
     * @param string $csv_file_path
     * @return Generator yields associative arrays (header => value)
     */
    protected function streamJudgemeCSV(string $csv_file_path) : Generator
    {
        if (!\file_exists($csv_file_path)) {
            return;
        }
        if (($handle = \fopen($csv_file_path, 'r')) === \false) {
            return;
        }
        $headers = \fgetcsv($handle);
        if (!$headers) {
            \fclose($handle);
            return;
        }
        // Clean BOM from headers
        $headers = \array_map(fn($h) => \preg_replace('/^\\xEF\\xBB\\xBF/', '', (string) $h), $headers);
        while (($row = \fgetcsv($handle)) !== \false) {
            if (!empty(\array_filter($row))) {
                (yield \array_combine($headers, $row));
            }
        }
        \fclose($handle);
    }
    /**
     * Import wrapper that returns success/failure without throwing.
     */
    protected function importSingleReviewWithStatus(array $review) : array
    {
        try {
            return $this->importSingleReview($review);
        } catch (Throwable $e) {
            return ['success' => \false, 'error' => $e->getMessage()];
        }
    }
    /**
     * Insert a single review into WordPress as a comment + meta.
     * Uses a sha1 hash stored in comment meta to avoid duplicates reliably.
     */
    protected function importSingleReview(array $review) : array
    {
        $product_id = (int) ($review['product_id'] ?? 0);
        if (!$product_id || get_post_type($product_id) !== 'product') {
            return ['success' => \false];
        }
        $comment_content = sanitize_text_field($review['body'] ?? '');
        $author = sanitize_text_field($review['reviewer_name'] ?? 'Anonymous');
        $author_email = sanitize_email($review['reviewer_email'] ?? '');
        $title = (string) ($review['title'] ?? '');
        $rating = (int) ($review['rating'] ?? 0);
        $comment_date = \gmdate('Y-m-d H:i:s', \strtotime($review['review_date'] ?? current_time('mysql')));
        $ip_address = sanitize_text_field($review['ip_address'] ?? '');
        // Create a stable hash for duplicate detection (product|email|body)
        $hashSource = \sprintf('%d|%s|%s', $product_id, $author_email, $comment_content);
        $hash = \sha1($hashSource);
        $existing_comment_id = null;
        // Check existing by hash (fast and reliable if meta exists)
        $existing_by_hash = get_comments(['post_id' => $product_id, 'meta_key' => 'rvx_judgeme_hash', 'meta_value' => $hash, 'number' => 1]);
        if (!empty($existing_by_hash)) {
            $existing_comment_id = $existing_by_hash[0]->comment_ID;
        } else {
            // As a fallback, also check author_email + identical content (older method)
            if ($author_email) {
                $existing = get_comments(['post_id' => $product_id, 'author_email' => $author_email, 'parent' => 0, 'comment_type' => 'review', 'search' => $comment_content, 'number' => 1]);
                if (!empty($existing)) {
                    $existing_comment_id = $existing[0]->comment_ID;
                    // Optionally, add the hash to this existing comment for future checks
                    add_comment_meta($existing_comment_id, 'rvx_judgeme_hash', $hash, \true);
                }
            }
        }
        if ($existing_comment_id) {
            // Review exists; now check if reply needs to be added
            if (!empty($review['reply'])) {
                $reply_content = sanitize_text_field($review['reply']);
                // Check for existing reply by parent and content search
                $existing_reply = get_comments(['parent' => $existing_comment_id, 'comment_type' => 'review', 'search' => $reply_content, 'number' => 1]);
                if (empty($existing_reply)) {
                    // No matching reply found; insert it
                    wp_insert_comment(['comment_post_ID' => $product_id, 'comment_parent' => $existing_comment_id, 'comment_author' => 'Shop Owner', 'comment_content' => $reply_content, 'comment_type' => 'review', 'comment_approved' => 1, 'comment_date' => \gmdate('Y-m-d H:i:s', \strtotime($review['reply_date'] ?? $comment_date))]);
                }
            }
            // If existing reply found, we skip (assume no update needed)
            return ['success' => \true];
        }
        // No existing review; insert new one
        // Insert comment
        $comment_id = wp_insert_comment(['comment_post_ID' => $product_id, 'comment_author' => $author, 'comment_author_email' => $author_email, 'comment_content' => $comment_content, 'comment_type' => 'review', 'comment_approved' => 1, 'comment_author_IP' => $ip_address, 'comment_date' => $comment_date, 'comment_meta' => ['rating' => $rating, 'reviewx_title' => $title, 'verified' => 1, 'is_recommended' => 1, 'reviewx_recommended' => 1, 'rvx_review_version' => 'v2', 'rvx_import_source' => 'judgeme', 'rvx_judgeme_hash' => $hash]]);
        if (\is_wp_error($comment_id) || !$comment_id) {
            return ['success' => \false];
        }
        // Explicitly trigger aggregation
        \Rvx\CPT\CptAverageRating::update_average_rating($product_id);
        // Insert reply if present
        if (!empty($review['reply'])) {
            wp_insert_comment(['comment_post_ID' => $product_id, 'comment_parent' => $comment_id, 'comment_author' => 'Shop Owner', 'comment_content' => sanitize_text_field($review['reply']), 'comment_type' => 'review', 'comment_approved' => 1, 'comment_date' => \gmdate('Y-m-d H:i:s', \strtotime($review['reply_date'] ?? $comment_date))]);
        }
        // Handle attachments
        if (!empty($review['picture_urls'])) {
            $media_urls = \array_map('trim', \explode(',', $review['picture_urls']));
            $media_data = [];
            foreach ($media_urls as $url) {
                $media = $this->downloadJudgemeMediaToLibrary($url, $comment_id);
                if (!empty($media['url'])) {
                    $media_data[] = $media['url'];
                }
            }
            if (!empty($media_data)) {
                add_comment_meta($comment_id, 'reviewx_attachments', $media_data);
            }
        }
        return ['success' => \true];
    }
    protected function downloadJudgemeMediaToLibrary(string $url, int $comment_id = 0) : array
    {
        if (empty($url) || !\filter_var($url, \FILTER_VALIDATE_URL)) {
            return ['id' => 0, 'url' => ''];
        }
        if (!\function_exists('Rvx\\media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        $tmp_file = download_url($url);
        if (\is_wp_error($tmp_file)) {
            return ['id' => 0, 'url' => ''];
        }
        $file_array = ['name' => \basename(\parse_url($url, \PHP_URL_PATH)), 'tmp_name' => $tmp_file];
        $media_id = media_handle_sideload($file_array, 0);
        if (\is_wp_error($media_id)) {
            @\unlink($file_array['tmp_name']);
            return ['id' => 0, 'url' => ''];
        }
        if ($comment_id > 0) {
            add_post_meta($media_id, '_rvx_source_comment', $comment_id);
        }
        return ['id' => $media_id, 'url' => wp_get_attachment_url($media_id)];
    }
    protected function determineChunkSize(int $total) : int
    {
        if ($total < 100) {
            return 5;
        }
        if ($total < 1000) {
            return 10;
        }
        return 20;
    }
    protected function getCsvFilePath() : string
    {
        return $this->csvFilePath;
    }
}
