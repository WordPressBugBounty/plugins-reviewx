<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Api\ReviewImportAndExportApi;
use ReviewX\CPT\CptHelper;
use ReviewX\Services\Api\LoginService;
use ReviewX\Api\AuthApi;
use ReviewX\Utilities\Helper;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Utilities\UploadMimeSupport;
use Exception;
class ImportExportServices extends \ReviewX\Services\Service
{
    private const IMPORT_PROGRESS_TTL = 3600;
    private const IMPORT_PROCESS_LOCK_TTL = 45;
    private const IMPORT_STATE_PREFIX = 'rvx_review_import_state_';
    private const IMPORT_PROCESS_LOCK_PREFIX = 'rvx_review_import_lock_';
    public const IMPORT_EVENT_HOOK = 'rvx_process_review_import_batch';
    private const IMPORT_BATCH_SIZE = 15;
    private const IMPORT_BATCH_RUNTIME = 12;
    private \ReviewX\Services\DataSyncService $dataSyncService;
    private \ReviewX\Services\CacheServices $cacheServices;
    private LoginService $loginService;
    private \ReviewX\Services\ReviewDuplicateService $reviewDuplicateService;
    protected static int $suspendCommentSideEffects = 0;
    protected array $resolvedImportPostIds = [];
    public function __construct()
    {
        $this->dataSyncService = new \ReviewX\Services\DataSyncService();
        $this->cacheServices = new \ReviewX\Services\CacheServices();
        $this->loginService = new LoginService();
        $this->reviewDuplicateService = new \ReviewX\Services\ReviewDuplicateService();
    }
    public static function withCommentSideEffectsSuspended(callable $callback)
    {
        self::$suspendCommentSideEffects++;
        try {
            return $callback();
        } finally {
            self::$suspendCommentSideEffects = \max(0, self::$suspendCommentSideEffects - 1);
        }
    }
    public static function shouldSuspendCommentSideEffects() : bool
    {
        return self::$suspendCommentSideEffects > 0;
    }
    protected function logImportTrace(string $message, string $context = 'debug', array $meta = []) : void
    {
    }
    protected function summarizeReviewRow(array $reviewData, array $map = []) : array
    {
        $productIdColumn = $map['product_id'] ?? null;
        $customerNameKey = $map['customer_name'] ?? null;
        $feedbackKey = $map['feedback'] ?? null;
        $ratingKey = $map['rating'] ?? null;
        $createdAtKey = $map['created_at'] ?? null;
        $replyKey = $map['review_reply'] ?? null;
        $feedback = $feedbackKey && isset($reviewData[$feedbackKey]) ? (string) $reviewData[$feedbackKey] : '';
        $reply = $replyKey && isset($reviewData[$replyKey]) ? (string) $reviewData[$replyKey] : '';
        return ['row_number' => isset($reviewData['_import_row_number']) ? (int) $reviewData['_import_row_number'] : 0, 'product_id' => $productIdColumn && isset($reviewData[$productIdColumn]) ? (string) $reviewData[$productIdColumn] : null, 'customer_name' => $customerNameKey && isset($reviewData[$customerNameKey]) ? (string) $reviewData[$customerNameKey] : null, 'rating' => $ratingKey && isset($reviewData[$ratingKey]) ? (string) $reviewData[$ratingKey] : null, 'created_at' => $createdAtKey && isset($reviewData[$createdAtKey]) ? (string) $reviewData[$createdAtKey] : null, 'has_feedback' => $feedback !== '', 'feedback_length' => \strlen($feedback), 'has_reply' => $reply !== ''];
    }
    public function importSupportedAppStore($data)
    {
        return (new ReviewImportAndExportApi())->importSupportedAppStore($data);
    }
    public function importStore($request)
    {
        $files = $request->get_file_params();
        $data = $request->get_params();
        $userId = $this->resolveImportUserId();
        $this->logImportTrace('Import endpoint received request.', 'debug', ['user_id' => $userId, 'file_name' => $files['file']['name'] ?? null, 'file_size' => $files['file']['size'] ?? null, 'map_keys' => \array_keys((array) ($data['map'] ?? [])), 'status' => $data['status'] ?? null, 'verified' => !empty($data['verified'])]);
        $response = $this->importReviewStore($files, $data);
        $this->logImportTrace('WordPress import phase completed. Starting ReviewX sync.', 'debug', $response);
        global $wpdb;
        $rvxSites = \esc_sql($wpdb->prefix . 'rvx_sites');
        (new \ReviewX\Handlers\ReviewXInit\LoadReviewxCreateSiteTable())->init();
        \set_transient('rvx_reset_sync_flag', \true, 300);
        $cacheKey = 'rvx_site_uid';
        $uid = \wp_cache_get($cacheKey, 'reviewx');
        if ($uid === \false) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table read
            $uid = $wpdb->get_var('SELECT uid FROM `' . $rvxSites . '` ORDER BY id DESC LIMIT 1');
            if ($uid) {
                \wp_cache_set($cacheKey, $uid, 'reviewx', 86400);
            }
        }
        $syncSuccess = \true;
        if ($uid) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table update, no standard WP API available
            $wpdb->update($rvxSites, ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
            $enabledPostTypes = (new CptHelper())->usedCPTOnSync('used');
            foreach ($enabledPostTypes as $postType) {
                $syncSuccess = $this->dataSyncService->dataSync('default', $postType) && $syncSuccess;
            }
        } else {
            $syncSuccess = \false;
        }
        $this->logImportTrace('ReviewX sync phase finished.', $syncSuccess ? 'debug' : 'warning', ['sync_success' => $syncSuccess, 'uid_found' => !empty($uid), 'total' => (int) ($response['total'] ?? 0), 'success' => (int) ($response['success'] ?? 0), 'failed' => (int) ($response['failed'] ?? 0), 'duplicates' => (int) ($response['duplicates'] ?? 0)]);
        $this->cacheServices->removeCache();
        $this->loginService->resetPostMeta();
        \delete_transient('rvx_admin_aggregation');
        $this->reviewDuplicateService->clearDuplicateReviewScanCache();
        $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => $syncSuccess ? 'completed' : 'warning', 'total_rows' => (int) ($response['total'] ?? 0), 'processed_rows' => (int) ($response['total'] ?? 0), 'success_rows' => (int) ($response['success'] ?? 0), 'failed_rows' => (int) ($response['failed'] ?? 0), 'duplicate_rows' => (int) ($response['duplicates'] ?? 0), 'reply_rows' => (int) ($response['reply_success'] ?? 0) + (int) ($response['reply_failed'] ?? 0), 'reply_success_rows' => (int) ($response['reply_success'] ?? 0), 'reply_failed_rows' => (int) ($response['reply_failed'] ?? 0), 'percentage' => 100, 'message' => $syncSuccess ? \__('Review import completed and synced successfully.', 'reviewx') : \__('Review import completed, but starting the regular ReviewX sync process failed.', 'reviewx'), 'completed_at' => \time()], $userId);
        $importMessage = !empty($response['duplicates']) ? \sprintf(
            /* translators: 1: imported review count, 2: duplicate review count */
            \__('WordPress import success! Imported %1$d reviews and skipped %2$d duplicates. Initiating synchronization with ReviewX Cloud...', 'reviewx'),
            (int) ($response['success'] ?? 0),
            (int) ($response['duplicates'] ?? 0)
        ) : \__('WordPress import success! Initiating synchronization with ReviewX Cloud...', 'reviewx');
        return ['status' => 'success', 'message' => $importMessage, 'data' => $response];
    }
    public function getImportProgress() : array
    {
        return $this->getImportProgressForUser();
    }
    public function importReviewStore($files, $data)
    {
        $userId = $this->resolveImportUserId();
        $filePath = $files['file']['tmp_name'] ?? '';
        if ($filePath === '' || !\file_exists($filePath)) {
            $this->logImportTrace('Uploaded CSV file is missing before import starts.', 'error', ['user_id' => $userId, 'file_name' => $files['file']['name'] ?? null]);
            throw new Exception(\esc_html__('The uploaded CSV file is missing.', 'reviewx'));
        }
        $this->deleteImportState($userId);
        $this->releaseImportProcessLock($userId);
        $request = $data;
        $successCount = 0;
        $duplicateCount = 0;
        $failedCount = 0;
        $replySuccessCount = 0;
        $replyFailedCount = 0;
        $wpReviewIds = [];
        $affectedPostIds = [];
        $totalRows = $this->countCsvDataRows($filePath);
        $this->logImportTrace('Starting direct CSV import into WordPress.', 'debug', ['user_id' => $userId, 'file_name' => $files['file']['name'] ?? null, 'file_path' => $filePath, 'total_rows' => $totalRows, 'status' => $request['status'] ?? null, 'verified' => !empty($request['verified']), 'map' => $request['map'] ?? []]);
        $this->setImportProgress(['is_importing' => \true, 'is_complete' => \false, 'stage' => 'importing', 'total_rows' => $totalRows, 'processed_rows' => 0, 'success_rows' => 0, 'failed_rows' => 0, 'duplicate_rows' => 0, 'reply_rows' => 0, 'reply_success_rows' => 0, 'reply_failed_rows' => 0, 'percentage' => 0, 'message' => \__('Preparing review import...', 'reviewx'), 'started_at' => \time()], $userId);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Large CSVs are streamed directly for import.
        $handle = \fopen($filePath, 'r');
        if ($handle === \false) {
            $this->logImportTrace('Failed to open uploaded CSV for import.', 'error', ['file_name' => $files['file']['name'] ?? null, 'file_path' => $filePath]);
            $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => 'failed', 'failed_rows' => $totalRows, 'percentage' => 100, 'message' => \__('Unable to open the CSV file for import.', 'reviewx')], $userId);
            return ['total' => 0, 'success' => 0, 'failed' => 0, 'duplicates' => 0, 'reply_success' => 0, 'reply_failed' => 0];
        }
        $header = \fgetcsv($handle, 0, ',', '"', '\\');
        if (!\is_array($header) || empty($header)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the CSV stream handle.
            \fclose($handle);
            $this->logImportTrace('Uploaded CSV header row is empty or invalid.', 'error', ['file_name' => $files['file']['name'] ?? null]);
            $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => 'failed', 'failed_rows' => $totalRows, 'percentage' => 100, 'message' => \__('The uploaded CSV file is empty or invalid.', 'reviewx')], $userId);
            return ['total' => 0, 'success' => 0, 'failed' => 0, 'duplicates' => 0, 'reply_success' => 0, 'reply_failed' => 0];
        }
        $header = $this->normalizeCsvHeader($header);
        $this->logImportTrace('CSV header parsed successfully.', 'debug', ['header' => $header]);
        $chunkSize = 50;
        $currentChunk = [];
        $csvRowNumber = 1;
        while (($row = \fgetcsv($handle, 0, ',', '"', '\\')) !== \false) {
            $csvRowNumber++;
            if ($row === [null] || $row === \false) {
                continue;
            }
            if (\count($header) !== \count($row)) {
                $this->logImportTrace('CSV column count mismatch detected; normalizing row to header width.', 'warning', ['row_number' => $csvRowNumber, 'header_columns' => \count($header), 'row_columns' => \count($row)]);
                if (\count($row) < \count($header)) {
                    $row = \array_pad($row, \count($header), '');
                } else {
                    $row = \array_slice($row, 0, \count($header));
                }
            }
            $reviewData = \array_combine($header, $row);
            if (!\is_array($reviewData)) {
                $failedCount++;
                $this->logImportTrace('Failed to map CSV row to header columns.', 'error', ['row_number' => $csvRowNumber, 'raw_row' => $row]);
                $this->updateImportProgressCounts($totalRows, $successCount, $failedCount, $duplicateCount, $replySuccessCount, $replyFailedCount, $userId);
                continue;
            }
            $reviewData['_import_row_number'] = $csvRowNumber;
            $productIdColumn = $request['map']['product_id'] ?? null;
            if ($productIdColumn && isset($reviewData[$productIdColumn])) {
                $affectedPostIds[] = (int) $reviewData[$productIdColumn];
            }
            $currentChunk[] = $reviewData;
            if (\count($currentChunk) >= $chunkSize) {
                $this->logImportTrace('Processing CSV chunk.', 'debug', ['chunk_size' => \count($currentChunk), 'first_row' => $currentChunk[0]['_import_row_number'] ?? null, 'last_row' => $currentChunk[\count($currentChunk) - 1]['_import_row_number'] ?? null]);
                $results = $this->processReviewBatch($currentChunk, $request);
                $successCount += (int) ($results['success'] ?? 0);
                $duplicateCount += (int) ($results['duplicates'] ?? 0);
                $failedCount += (int) ($results['failed'] ?? 0);
                $replySuccessCount += (int) ($results['reply_success'] ?? 0);
                $replyFailedCount += (int) ($results['reply_failed'] ?? 0);
                $wpReviewIds = \array_merge($wpReviewIds, $results['ids'] ?? []);
                $this->logImportTrace('Finished CSV chunk.', 'debug', ['chunk_results' => $results, 'running_success' => $successCount, 'running_failed' => $failedCount, 'running_duplicates' => $duplicateCount]);
                $this->updateImportProgressCounts($totalRows, $successCount, $failedCount, $duplicateCount, $replySuccessCount, $replyFailedCount, $userId);
                $currentChunk = [];
            }
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the CSV stream handle.
        \fclose($handle);
        if (!empty($currentChunk)) {
            $this->logImportTrace('Processing final CSV chunk.', 'debug', ['chunk_size' => \count($currentChunk), 'first_row' => $currentChunk[0]['_import_row_number'] ?? null, 'last_row' => $currentChunk[\count($currentChunk) - 1]['_import_row_number'] ?? null]);
            $results = $this->processReviewBatch($currentChunk, $request);
            $successCount += (int) ($results['success'] ?? 0);
            $duplicateCount += (int) ($results['duplicates'] ?? 0);
            $failedCount += (int) ($results['failed'] ?? 0);
            $replySuccessCount += (int) ($results['reply_success'] ?? 0);
            $replyFailedCount += (int) ($results['reply_failed'] ?? 0);
            $wpReviewIds = \array_merge($wpReviewIds, $results['ids'] ?? []);
            $this->logImportTrace('Finished final CSV chunk.', 'debug', ['chunk_results' => $results, 'running_success' => $successCount, 'running_failed' => $failedCount, 'running_duplicates' => $duplicateCount]);
            $this->updateImportProgressCounts($totalRows, $successCount, $failedCount, $duplicateCount, $replySuccessCount, $replyFailedCount, $userId);
        }
        $affectedPostIds = \array_values(\array_unique(\array_filter(\array_map('intval', $affectedPostIds))));
        $this->logImportTrace('Refreshing average ratings for imported posts.', 'debug', ['affected_post_ids' => $affectedPostIds]);
        foreach ($affectedPostIds as $postId) {
            \ReviewX\CPT\CptAverageRating::update_average_rating($postId);
        }
        $this->setImportProgress(['is_importing' => \true, 'is_complete' => \false, 'stage' => 'syncing', 'total_rows' => $totalRows, 'processed_rows' => $successCount + $failedCount + $duplicateCount, 'success_rows' => $successCount, 'failed_rows' => $failedCount, 'duplicate_rows' => $duplicateCount, 'reply_rows' => $replySuccessCount + $replyFailedCount, 'reply_success_rows' => $replySuccessCount, 'reply_failed_rows' => $replyFailedCount, 'percentage' => 100, 'message' => \__('Review import completed. Starting the regular ReviewX sync process...', 'reviewx')], $userId);
        $historyState = ['file_name' => \basename($files['file']['name'] ?? 'reviews.csv'), 'map' => $request['map'] ?? [], 'wp_review_ids' => \array_values(\array_unique(\array_map('intval', $wpReviewIds))), 'total_rows' => $totalRows, 'success_rows' => $successCount, 'failed_rows' => $failedCount, 'duplicate_rows' => $duplicateCount, 'reply_success_rows' => $replySuccessCount, 'reply_failed_rows' => $replyFailedCount];
        $this->logImportHistoryToSaas($historyState);
        $this->logImportTrace('Direct WordPress import finished.', 'debug', ['total' => $totalRows, 'success' => $successCount, 'failed' => $failedCount, 'duplicates' => $duplicateCount, 'reply_success' => $replySuccessCount, 'reply_failed' => $replyFailedCount, 'wp_review_ids_count' => \count($wpReviewIds)]);
        return ['total' => $totalRows, 'success' => $successCount, 'failed' => $failedCount, 'duplicates' => $duplicateCount, 'reply_success' => $replySuccessCount, 'reply_failed' => $replyFailedCount];
    }
    public function processScheduledImport($userId = null) : void
    {
        $userId = $this->resolveImportUserId($userId);
        if (!$this->acquireImportProcessLock($userId)) {
            $this->logImportTrace('Skipped scheduled import batch because a lock already exists.', 'debug', ['user_id' => $userId]);
            return;
        }
        try {
            $state = $this->loadImportState($userId);
            $this->logImportTrace('Scheduled import processor invoked.', 'debug', ['user_id' => $userId, 'has_state' => !empty($state), 'state_keys' => \array_keys($state)]);
            if (empty($state['file_path']) || empty($state['header'])) {
                $this->logImportTrace('Scheduled import processor found no staged file/header state.', 'debug', ['user_id' => $userId]);
                return;
            }
            if (!\file_exists((string) $state['file_path'])) {
                $this->logImportTrace('Scheduled import staged file is missing.', 'error', ['user_id' => $userId, 'file_path' => $state['file_path']]);
                $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => 'failed', 'percentage' => 100, 'message' => \__('The staged CSV file could not be found. Please upload the CSV again.', 'reviewx')], $userId);
                $this->deleteImportState($userId);
                return;
            }
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Live batch imports may run for several seconds on shared hosting.
            \set_time_limit(0);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Streaming the staged CSV file keeps each batch lightweight.
            $handle = \fopen((string) $state['file_path'], 'r');
            if ($handle === \false) {
                $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => 'failed', 'percentage' => 100, 'message' => \__('Unable to open the staged CSV file for import.', 'reviewx')], $userId);
                $this->deleteImportState($userId);
                return;
            }
            $offset = isset($state['offset']) ? (int) $state['offset'] : 0;
            if ($offset > 0) {
                \fseek($handle, $offset);
            }
            $request = ['map' => $state['map'] ?? [], 'status' => $state['status'] ?? 1, 'verified' => !empty($state['verified'])];
            $header = \is_array($state['header']) ? $state['header'] : [];
            $startTime = \microtime(\true);
            $processedThisBatch = 0;
            while ($processedThisBatch < self::IMPORT_BATCH_SIZE && \microtime(\true) - $startTime < self::IMPORT_BATCH_RUNTIME && ($row = \fgetcsv($handle, 0, ',', '"', '\\')) !== \false) {
                $currentOffset = \ftell($handle);
                if ($currentOffset !== \false) {
                    $state['offset'] = (int) $currentOffset;
                }
                if ($row === [null] || $row === \false) {
                    continue;
                }
                if (\count($header) !== \count($row)) {
                    if (\count($row) < \count($header)) {
                        $row = \array_pad($row, \count($header), '');
                    } else {
                        $row = \array_slice($row, 0, \count($header));
                    }
                }
                $reviewData = \array_combine($header, $row);
                if (!\is_array($reviewData)) {
                    $state['failed_rows'] = (int) ($state['failed_rows'] ?? 0) + 1;
                    $processedThisBatch++;
                    $this->logImportTrace('Scheduled import failed to map row to header columns.', 'error', ['user_id' => $userId, 'processed_this_batch' => $processedThisBatch]);
                    continue;
                }
                $reviewData['_import_row_number'] = (int) (($state['success_rows'] ?? 0) + ($state['failed_rows'] ?? 0) + ($state['duplicate_rows'] ?? 0) + 2);
                try {
                    $result = $this->processImportedReviewRow($reviewData, $request);
                } catch (\Throwable $throwable) {
                    $state['failed_rows'] = (int) ($state['failed_rows'] ?? 0) + 1;
                    $processedThisBatch++;
                    $this->logImportTrace('Review import row failed: ' . $throwable->getMessage() . ' at ' . $throwable->getFile() . ':' . $throwable->getLine(), 'error');
                    continue;
                }
                if (($result['status'] ?? '') === 'duplicate') {
                    $state['duplicate_rows'] = (int) ($state['duplicate_rows'] ?? 0) + 1;
                } elseif (($result['status'] ?? '') === 'inserted' && !empty($result['comment_id'])) {
                    $state['success_rows'] = (int) ($state['success_rows'] ?? 0) + 1;
                    $state['wp_review_ids'][] = (int) $result['comment_id'];
                    if (!empty($result['post_id'])) {
                        $state['affected_post_ids'][] = (int) $result['post_id'];
                    }
                } else {
                    $state['failed_rows'] = (int) ($state['failed_rows'] ?? 0) + 1;
                }
                $state['reply_success_rows'] = (int) ($state['reply_success_rows'] ?? 0) + (int) ($result['reply_success'] ?? 0);
                $state['reply_failed_rows'] = (int) ($state['reply_failed_rows'] ?? 0) + (int) ($result['reply_failed'] ?? 0);
                $processedThisBatch++;
            }
            $isComplete = \feof($handle);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the staged CSV handle after a batch.
            \fclose($handle);
            $state['wp_review_ids'] = \array_values(\array_unique(\array_map('intval', $state['wp_review_ids'] ?? [])));
            $this->saveImportState($state, $userId);
            $this->logImportTrace('Scheduled import batch processed.', 'debug', ['user_id' => $userId, 'processed_this_batch' => $processedThisBatch, 'is_complete' => $isComplete, 'success_rows' => (int) ($state['success_rows'] ?? 0), 'failed_rows' => (int) ($state['failed_rows'] ?? 0), 'duplicate_rows' => (int) ($state['duplicate_rows'] ?? 0)]);
            if (!$isComplete) {
                $this->updateImportProgressCounts((int) ($state['total_rows'] ?? 0), (int) ($state['success_rows'] ?? 0), (int) ($state['failed_rows'] ?? 0), (int) ($state['duplicate_rows'] ?? 0), (int) ($state['reply_success_rows'] ?? 0), (int) ($state['reply_failed_rows'] ?? 0), $userId);
                return;
            }
            $this->finalizeImportState($state, $userId);
        } finally {
            $this->releaseImportProcessLock($userId);
        }
    }
    private function processReviewBatch(array $reviews, array $request) : array
    {
        $ids = [];
        $total = 0;
        $success = 0;
        $duplicates = 0;
        $failed = 0;
        $replySuccess = 0;
        $replyFailed = 0;
        foreach ($reviews as $reviewData) {
            $total++;
            $this->logImportTrace('Processing individual review row.', 'debug', $this->summarizeReviewRow($reviewData, $request['map'] ?? []));
            try {
                $insertResult = $this->processImportedReviewRow($reviewData, $request);
                if (($insertResult['status'] ?? '') === 'duplicate') {
                    $duplicates++;
                    $this->logImportTrace('Review row marked as duplicate.', 'debug', ['row' => $this->summarizeReviewRow($reviewData, $request['map'] ?? []), 'comment_id' => (int) ($insertResult['comment_id'] ?? 0)]);
                    continue;
                }
                $commentId = (int) ($insertResult['comment_id'] ?? 0);
                if (($insertResult['status'] ?? '') === 'inserted' && $commentId > 0) {
                    $ids[] = $commentId;
                    $success++;
                    $this->logImportTrace('Review row inserted successfully.', 'debug', ['row' => $this->summarizeReviewRow($reviewData, $request['map'] ?? []), 'comment_id' => $commentId, 'reply_success' => (int) ($insertResult['reply_success'] ?? 0), 'reply_failed' => (int) ($insertResult['reply_failed'] ?? 0)]);
                } else {
                    $failed++;
                    $this->logImportTrace('Review row finished without a comment insert.', 'warning', ['row' => $this->summarizeReviewRow($reviewData, $request['map'] ?? []), 'insert_result' => $insertResult]);
                }
                $replySuccess += (int) ($insertResult['reply_success'] ?? 0);
                $replyFailed += (int) ($insertResult['reply_failed'] ?? 0);
            } catch (\Throwable $e) {
                $failed++;
                $this->logImportTrace('Review row threw an exception during processing.', 'error', ['row' => $this->summarizeReviewRow($reviewData, $request['map'] ?? []), 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            }
        }
        return ['total' => $total, 'success' => $success, 'duplicates' => $duplicates, 'failed' => $failed, 'reply_success' => $replySuccess, 'reply_failed' => $replyFailed, 'ids' => $ids];
    }
    private function processImportedReviewRow(array $reviewData, array $request) : array
    {
        $map = $request['map'] ?? [];
        $postType = !empty($reviewData['Post_Type']) ? \sanitize_key((string) $reviewData['Post_Type']) : 'product';
        $productIdColumn = $map['product_id'] ?? null;
        $rawProductIdentifier = $productIdColumn && isset($reviewData[$productIdColumn]) ? \trim((string) $reviewData[$productIdColumn]) : '';
        $wpProductId = $this->resolveImportedPostId($rawProductIdentifier, $postType);
        if (!$wpProductId) {
            $this->logImportTrace('Skipping review row because the mapped product identifier could not be resolved.', 'warning', ['row' => $this->summarizeReviewRow($reviewData, $map), 'product_identifier' => $rawProductIdentifier, 'post_type' => $postType, 'map' => $map]);
            return ['status' => 'failed', 'comment_id' => 0, 'post_id' => 0, 'reply_success' => 0, 'reply_failed' => 0];
        }
        \delete_transient("rvx_{$wpProductId}_latest_reviews");
        \delete_transient("rvx_{$wpProductId}_latest_reviews_insight");
        return $this->insertReview($wpProductId, $reviewData, $request, $postType);
    }
    public function insertReview($reviews_id, $review_data, $request, $post_type)
    {
        $this->logImportTrace('Preparing WordPress comment insert.', 'debug', ['post_id' => (int) $reviews_id, 'post_type' => $post_type, 'row' => $this->summarizeReviewRow($review_data, $request['map'] ?? [])]);
        $mediaArray = [];
        $map = $request['map'] ?? [];
        $attachmentKey = $map['attachment'] ?? null;
        if ($attachmentKey && isset($review_data[$attachmentKey]) && !empty($review_data[$attachmentKey])) {
            $mediaArray = $this->parseAttachmentUrls((string) $review_data[$attachmentKey]);
        }
        $comment_type = 'review';
        if (!empty($post_type) && \strtolower($post_type) != 'product') {
            $comment_type = 'comment';
        }
        $customerNameKey = $request['map']['customer_name'] ?? null;
        $customerEmailKey = $request['map']['customer_email'] ?? null;
        $feedbackKey = $request['map']['feedback'] ?? null;
        $createdAtKey = $request['map']['created_at'] ?? null;
        $comment_data = ['comment_post_ID' => $reviews_id, 'comment_author' => $customerNameKey && isset($review_data[$customerNameKey]) ? $review_data[$customerNameKey] : 'Anonymous', 'comment_author_email' => $customerEmailKey && isset($review_data[$customerEmailKey]) ? $review_data[$customerEmailKey] : '', 'comment_content' => $feedbackKey && isset($review_data[$feedbackKey]) ? $review_data[$feedbackKey] : '', 'comment_date' => $createdAtKey && !empty($review_data[$createdAtKey]) && \strtotime($review_data[$createdAtKey]) !== \false ? \wp_date('Y-m-d H:i:s', \strtotime($review_data[$createdAtKey])) : \wp_date('Y-m-d H:i:s'), 'comment_date_gmt' => $createdAtKey && !empty($review_data[$createdAtKey]) && \strtotime($review_data[$createdAtKey]) !== \false ? \gmdate('Y-m-d H:i:s', \strtotime($review_data[$createdAtKey])) : \gmdate('Y-m-d H:i:s'), 'comment_approved' => Helper::arrayGet($request, 'status'), 'comment_type' => $comment_type, 'comment_parent' => 0, 'comment_author_url' => ''];
        $duplicateCommentId = $this->reviewDuplicateService->findDuplicateCommentId($comment_data);
        if ($duplicateCommentId > 0) {
            $this->logImportTrace('Duplicate review detected before insert.', 'debug', ['post_id' => (int) $reviews_id, 'duplicate_comment_id' => $duplicateCommentId, 'row' => $this->summarizeReviewRow($review_data, $request['map'] ?? [])]);
            return ['status' => 'duplicate', 'comment_id' => $duplicateCommentId, 'post_id' => (int) $reviews_id];
        }
        $comment_id = self::withCommentSideEffectsSuspended(static function () use($comment_data) {
            return \wp_insert_comment($comment_data);
        });
        $this->logImportTrace('wp_insert_comment returned.', $comment_id && !\is_wp_error($comment_id) ? 'debug' : 'warning', ['post_id' => (int) $reviews_id, 'comment_id' => $comment_id && !\is_wp_error($comment_id) ? (int) $comment_id : 0, 'comment_author' => $comment_data['comment_author'] ?? null, 'comment_type' => $comment_data['comment_type'] ?? null]);
        if ($comment_id && !\is_wp_error($comment_id)) {
            $titleKey = $request['map']['review_title'] ?? null;
            $titleValue = $titleKey && isset($review_data[$titleKey]) ? $review_data[$titleKey] : null;
            \update_comment_meta($comment_id, 'rvx_title', $titleValue);
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
                $this->logImportTrace('Processing attachments for imported review.', 'debug', ['comment_id' => (int) $comment_id, 'attachment_count' => \count($mediaArray)]);
                foreach ($mediaArray as $url) {
                    $processed = $this->sideloadAttachment($url);
                    if (!empty($processed) && \filter_var($processed, \FILTER_VALIDATE_URL)) {
                        $processedMedia[] = $processed;
                    }
                }
            }
            $processedMedia = \array_values(\array_unique($processedMedia));
            \update_comment_meta($comment_id, 'reviewx_attachments', $processedMedia);
            \update_comment_meta($comment_id, 'rvx_attachments', $processedMedia);
            \update_comment_meta($comment_id, 'verified', Helper::arrayGet($request, 'verified'));
            \update_comment_meta($comment_id, 'rvx_review_version', 'v2');
            // Handle Review Reply
            $replySuccess = 0;
            $replyFailed = 0;
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
                $replyData = ['comment_post_ID' => $reviews_id, 'comment_author' => $currentUser ? $currentUser->display_name : 'Shop Owner', 'comment_author_email' => $currentUser ? $currentUser->user_email : \get_option('admin_email'), 'comment_content' => $review_data[$replyContentColumn], 'comment_type' => 'comment', 'comment_parent' => $comment_id, 'comment_approved' => 1, 'comment_date' => \wp_date('Y-m-d H:i:s', $repliedAtTime), 'comment_date_gmt' => \gmdate('Y-m-d H:i:s', $repliedAtTime)];
                $replyId = self::withCommentSideEffectsSuspended(static function () use($replyData) {
                    return \wp_insert_comment($replyData);
                });
                if ($replyId && !\is_wp_error($replyId)) {
                    $replySuccess = 1;
                    $this->logImportTrace('Imported reply comment successfully.', 'debug', ['parent_comment_id' => (int) $comment_id, 'reply_comment_id' => (int) $replyId]);
                } else {
                    $replyFailed = 1;
                    $this->logImportTrace('Imported reply comment failed.', 'error', ['parent_comment_id' => (int) $comment_id]);
                }
            }
            return ['status' => 'inserted', 'comment_id' => (int) $comment_id, 'post_id' => (int) $reviews_id, 'reply_success' => $replySuccess, 'reply_failed' => $replyFailed];
        }
        $this->logImportTrace('WordPress comment insert failed.', 'error', ['post_id' => (int) $reviews_id, 'row' => $this->summarizeReviewRow($review_data, $request['map'] ?? []), 'insert_result_type' => \is_object($comment_id) ? \get_class($comment_id) : \gettype($comment_id), 'insert_result' => \is_wp_error($comment_id) ? $comment_id->get_error_message() : $comment_id]);
        return ['status' => $comment_id && !\is_wp_error($comment_id) ? 'inserted' : 'failed', 'comment_id' => $comment_id && !\is_wp_error($comment_id) ? (int) $comment_id : 0, 'post_id' => $comment_id && !\is_wp_error($comment_id) ? (int) $reviews_id : 0, 'reply_success' => 0, 'reply_failed' => 0];
    }
    /**
     * Resolve a product post ID by SKU.
     *
     * @param string $sku
     * @return int
     */
    protected function resolveProductIdBySku(string $sku) : int
    {
        return $this->resolvePostIdBySku($sku, 'product');
    }
    protected function resolvePostIdBySku(string $sku, string $postType = 'product') : int
    {
        if ($sku === '') {
            return 0;
        }
        $args = [
            'post_type' => $postType,
            'post_status' => 'any',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- SKU lookup depends on product meta until there is a dedicated indexed identifier store.
            'meta_query' => [['key' => '_sku', 'value' => $sku, 'compare' => '=']],
            'fields' => 'ids',
            'posts_per_page' => 1,
            'no_found_rows' => \true,
        ];
        $posts = \get_posts($args);
        if (!empty($posts) && isset($posts[0])) {
            return (int) $posts[0];
        }
        return 0;
    }
    /**
     * Resolve a product post ID by title.
     *
     * @param string $title
     * @return int
     */
    protected function resolveProductIdByTitle(string $title) : int
    {
        return $this->resolvePostIdByTitle($title, 'product');
    }
    protected function resolvePostIdByTitle(string $title, string $postType = 'product') : int
    {
        if ($title === '') {
            return 0;
        }
        $args = ['post_type' => $postType, 'post_status' => 'any', 's' => $title, 'fields' => 'ids', 'posts_per_page' => 5, 'no_found_rows' => \true];
        $posts = \get_posts($args);
        if (!empty($posts)) {
            // Prefer exact title match
            foreach ($posts as $p) {
                if (\strcasecmp(\trim(get_the_title($p)), \trim($title)) === 0) {
                    return (int) $p;
                }
            }
            return (int) $posts[0];
        }
        return 0;
    }
    protected function resolvePostIdBySlug(string $slug, string $postType = 'product') : int
    {
        if ($slug === '') {
            return 0;
        }
        $posts = \get_posts(['name' => \sanitize_title($slug), 'post_type' => $postType, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => \true]);
        if (!empty($posts) && isset($posts[0])) {
            return (int) $posts[0];
        }
        return 0;
    }
    protected function resolveImportedPostId(string $identifier, string $postType = 'product') : int
    {
        $identifier = \trim($identifier);
        $postType = $postType !== '' ? $postType : 'product';
        if ($identifier === '') {
            return 0;
        }
        $cacheKey = \strtolower($postType . '|' . $identifier);
        if (\array_key_exists($cacheKey, $this->resolvedImportPostIds)) {
            return (int) $this->resolvedImportPostIds[$cacheKey];
        }
        $resolvedPostId = 0;
        if (\ctype_digit($identifier)) {
            $candidatePostId = (int) $identifier;
            $candidatePost = \get_post($candidatePostId);
            if ($candidatePost && $candidatePost->post_type === $postType) {
                $resolvedPostId = $candidatePostId;
            }
        }
        if (!$resolvedPostId && $postType === 'product' && \function_exists('wc_get_product_id_by_sku')) {
            $resolvedPostId = (int) \wc_get_product_id_by_sku($identifier);
        }
        if (!$resolvedPostId && $postType === 'product') {
            $resolvedPostId = $this->resolvePostIdBySku($identifier, $postType);
        }
        if (!$resolvedPostId) {
            $resolvedPostId = $this->resolvePostIdBySlug($identifier, $postType);
        }
        if (!$resolvedPostId) {
            $resolvedPostId = $this->resolvePostIdByTitle($identifier, $postType);
        }
        $this->resolvedImportPostIds[$cacheKey] = $resolvedPostId;
        return $resolvedPostId;
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
                if ((new \ReviewX\Services\ReviewService())->deleteCommentTreeInWp((int) $commentId)) {
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
    protected function normalizeCsvHeader(array $header) : array
    {
        if (isset($header[0])) {
            $header[0] = (string) \preg_replace('/^\\xEF\\xBB\\xBF/', '', (string) $header[0]);
        }
        return \array_map(static function ($value) {
            return \is_string($value) ? \trim($value) : '';
        }, $header);
    }
    protected function countCsvDataRows(string $filePath) : int
    {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Streaming the uploaded CSV keeps memory usage predictable for large imports.
        $handle = \fopen($filePath, 'r');
        if ($handle === \false) {
            return 0;
        }
        \fgetcsv($handle, 0, ',', '"', '\\');
        $total = 0;
        while (($row = \fgetcsv($handle, 0, ',', '"', '\\')) !== \false) {
            if ($row === [null]) {
                continue;
            }
            $hasValue = \false;
            foreach ($row as $value) {
                if (\trim((string) $value) !== '') {
                    $hasValue = \true;
                    break;
                }
            }
            if ($hasValue) {
                $total++;
            }
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the CSV stream handle.
        \fclose($handle);
        return $total;
    }
    protected function getImportProgressKey(?int $userId = null) : string
    {
        $userId = $this->resolveImportUserId($userId);
        return 'rvx_review_import_progress_' . ($userId > 0 ? $userId : 'guest');
    }
    protected function getImportProgressForUser(?int $userId = null) : array
    {
        $defaultState = ['is_importing' => \false, 'is_complete' => \false, 'stage' => 'idle', 'total_rows' => 0, 'processed_rows' => 0, 'success_rows' => 0, 'failed_rows' => 0, 'duplicate_rows' => 0, 'reply_rows' => 0, 'reply_success_rows' => 0, 'reply_failed_rows' => 0, 'percentage' => 0, 'message' => ''];
        $progress = \get_transient($this->getImportProgressKey($userId));
        if (!\is_array($progress)) {
            return $defaultState;
        }
        return \array_merge($defaultState, $progress);
    }
    protected function setImportProgress(array $progress, ?int $userId = null) : void
    {
        $current = \get_transient($this->getImportProgressKey($userId));
        if (!\is_array($current)) {
            $current = [];
        }
        \set_transient($this->getImportProgressKey($userId), \array_merge($current, $progress), self::IMPORT_PROGRESS_TTL);
    }
    protected function updateImportProgressCounts(int $totalRows, int $successCount, int $failedCount, int $duplicateCount, int $replySuccessCount, int $replyFailedCount, ?int $userId = null) : void
    {
        $processedRows = $successCount + $failedCount + $duplicateCount;
        $percentage = $totalRows > 0 ? (int) \min(99, \floor($processedRows / $totalRows * 100)) : 0;
        $this->setImportProgress(['is_importing' => \true, 'is_complete' => \false, 'stage' => 'importing', 'total_rows' => $totalRows, 'processed_rows' => $processedRows, 'success_rows' => $successCount, 'failed_rows' => $failedCount, 'duplicate_rows' => $duplicateCount, 'reply_rows' => $replySuccessCount + $replyFailedCount, 'reply_success_rows' => $replySuccessCount, 'reply_failed_rows' => $replyFailedCount, 'percentage' => $percentage, 'message' => \sprintf(
            /* translators: 1: processed review count, 2: total review count, 3: imported reply count */
            \__('Imported %1$d of %2$d review rows. Replies imported: %3$d.', 'reviewx'),
            $processedRows,
            $totalRows,
            $replySuccessCount
        )], $userId);
        $this->logImportTrace('Progress counters updated.', 'debug', ['user_id' => $this->resolveImportUserId($userId), 'total_rows' => $totalRows, 'processed_rows' => $processedRows, 'success_rows' => $successCount, 'failed_rows' => $failedCount, 'duplicate_rows' => $duplicateCount, 'reply_success_rows' => $replySuccessCount, 'reply_failed_rows' => $replyFailedCount, 'percentage' => $percentage]);
    }
    protected function parseAttachmentUrls(string $attachments) : array
    {
        $attachments = \trim(\html_entity_decode($attachments, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        if ($attachments === '') {
            return [];
        }
        $urls = [];
        $jsonDecoded = \json_decode($attachments, \true);
        if (\is_array($jsonDecoded)) {
            foreach ($jsonDecoded as $item) {
                if (\is_string($item) && \trim($item) !== '') {
                    $urls[] = \trim($item);
                }
            }
        } else {
            \preg_match_all('/(?:https?:)?\\/\\/[^\\s"\'<>|]+/i', $attachments, $matches);
            if (!empty($matches[0])) {
                $urls = $matches[0];
            } else {
                $normalized = \str_replace(["\r\n", "\r", "\n", ';', '|'], ',', $attachments);
                $urls = \str_getcsv($normalized, ',', '"', '\\');
            }
        }
        $cleaned = [];
        foreach ($urls as $url) {
            $url = \trim((string) $url, " \t\n\r\x00\v\"'");
            $url = \rtrim($url, ',;');
            if ($url === '') {
                continue;
            }
            if (\strpos($url, '//') === 0) {
                $url = 'https:' . $url;
            }
            $url = \str_replace(' ', '%20', $url);
            if (\filter_var($url, \FILTER_VALIDATE_URL)) {
                $cleaned[] = $url;
            }
        }
        return \array_values(\array_unique($cleaned));
    }
    protected function buildAttachmentFileName(string $url, string $tmpFile) : string
    {
        $cleanUrl = \strtok($url, '?');
        $path = (string) \wp_parse_url((string) $cleanUrl, \PHP_URL_PATH);
        $fileName = \sanitize_file_name((string) \basename($path));
        if ($fileName === '' || \strpos($fileName, '.') === \false) {
            $fileName = $fileName ?: 'reviewx-import-' . \substr(\md5($url), 0, 12);
            $detected = \wp_check_filetype_and_ext($tmpFile, $fileName);
            if (!empty($detected['ext']) && \is_string($detected['ext'])) {
                $fileName .= '.' . $detected['ext'];
            }
        }
        return $fileName;
    }
    /**
     * Sideload an attachment from a URL to the local media library.
     * 
     * @param string $url
     * @return string
     */
    protected function sideloadAttachment($url)
    {
        $url = \trim(\html_entity_decode((string) $url, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        $url = \trim($url, "\"'");
        if (\strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        $url = \str_replace(' ', '%20', $url);
        if (empty($url) || !\wp_http_validate_url($url)) {
            $this->logImportTrace('Invalid URL for sideload.', 'warning', ['url' => $url]);
            return '';
        }
        $existingAttachmentId = \attachment_url_to_postid($url);
        if ($existingAttachmentId > 0) {
            $existingAttachmentUrl = \wp_get_attachment_url($existingAttachmentId);
            if (\is_string($existingAttachmentUrl) && $existingAttachmentUrl !== '') {
                return $existingAttachmentUrl;
            }
        }
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Intentional deduplication by source URL before sideload
            'meta_query' => [['key' => '_rvx_source_url', 'value' => $url]],
            'posts_per_page' => 1,
            'fields' => 'ids',
        ];
        $existing = \get_posts($args);
        if (!empty($existing)) {
            return \wp_get_attachment_url($existing[0]);
        }
        if (!\function_exists('download_url')) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!\function_exists('media_handle_sideload')) {
            require_once \ABSPATH . 'wp-admin/includes/media.php';
            require_once \ABSPATH . 'wp-admin/includes/image.php';
        }
        // Skip sideloading for external video links (YouTube, Vimeo, etc.)
        $videoHosts = ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com'];
        $host = \wp_parse_url($url, \PHP_URL_HOST);
        foreach ($videoHosts as $videoHost) {
            if ($host && \strpos($host, $videoHost) !== \false) {
                $this->logImportTrace('Skipping sideload for remote video URL.', 'debug', ['url' => $url]);
                return $url;
            }
        }
        $tmp = \download_url($url, 45);
        if (\is_wp_error($tmp)) {
            $this->logImportTrace('Download failed for attachment.', 'error', ['url' => $url, 'error' => $tmp->get_error_message()]);
            return $url;
        }
        $file_name = $this->buildAttachmentFileName($url, $tmp);
        $file_array = ['name' => $file_name, 'tmp_name' => $tmp];
        // Use a manual sideload flow so imported webp files can pass the same
        // mime rules we already allow elsewhere in the plugin.
        $upload = UploadMimeSupport::withAllowedUploads(function () use($file_array) {
            return \wp_handle_sideload($file_array, UploadMimeSupport::getWpHandleUploadOverrides(['test_type' => \false]));
        });
        if (isset($upload['error'])) {
            $this->logImportTrace('wp_handle_sideload failed for attachment.', 'error', ['url' => $url, 'error' => $upload['error']]);
            global $wp_filesystem;
            if (!empty($wp_filesystem)) {
                $wp_filesystem->delete($tmp);
            } else {
                \wp_delete_file($tmp);
            }
            return $url;
        }
        $attachmentMimeType = $upload['type'] ?? '';
        if (!\is_string($attachmentMimeType) || $attachmentMimeType === '') {
            $detectedType = \wp_check_filetype((string) ($upload['file'] ?? ''), UploadMimeSupport::getAllowedUploadMimes());
            $attachmentMimeType = $detectedType['type'] ?? 'application/octet-stream';
        }
        $attachmentId = \wp_insert_attachment(['guid' => $upload['url'] ?? '', 'post_mime_type' => $attachmentMimeType, 'post_title' => \sanitize_text_field(\pathinfo($file_name, \PATHINFO_FILENAME)), 'post_content' => '', 'post_status' => 'inherit'], $upload['file'] ?? '');
        if (\is_wp_error($attachmentId) || !$attachmentId) {
            $errorMessage = \is_wp_error($attachmentId) ? $attachmentId->get_error_message() : 'Unknown attachment insert error';
            $this->logImportTrace('wp_insert_attachment failed for attachment.', 'error', ['url' => $url, 'error' => $errorMessage]);
            if (!empty($upload['file']) && \file_exists((string) $upload['file'])) {
                \wp_delete_file((string) $upload['file']);
            }
            return $url;
        }
        $attachmentMetadata = UploadMimeSupport::generateAttachmentMetadata((int) $attachmentId, (string) ($upload['file'] ?? ''), $attachmentMimeType);
        if (!empty($attachmentMetadata)) {
            \wp_update_attachment_metadata((int) $attachmentId, $attachmentMetadata);
        }
        // Store original source URL as metadata
        \update_post_meta((int) $attachmentId, '_rvx_source_url', $url);
        return \wp_get_attachment_url((int) $attachmentId) ?: $url;
    }
    protected function getImportStateKey(?int $userId = null) : string
    {
        $userId = $this->resolveImportUserId($userId);
        return self::IMPORT_STATE_PREFIX . ($userId > 0 ? $userId : 'guest');
    }
    protected function saveImportState(array $state, ?int $userId = null) : void
    {
        \update_option($this->getImportStateKey($userId), $state, \false);
    }
    protected function loadImportState(?int $userId = null) : array
    {
        $state = \get_option($this->getImportStateKey($userId), []);
        return \is_array($state) ? $state : [];
    }
    protected function deleteImportState(?int $userId = null) : void
    {
        \delete_option($this->getImportStateKey($userId));
        \wp_clear_scheduled_hook(self::IMPORT_EVENT_HOOK, [$this->resolveImportUserId($userId)]);
    }
    protected function resolveImportUserId($userId = null) : int
    {
        if ($userId !== null && (int) $userId > 0) {
            return (int) $userId;
        }
        $currentUserId = \get_current_user_id();
        return $currentUserId > 0 ? (int) $currentUserId : 0;
    }
    protected function stageImportFile(array $file) : string
    {
        $sourcePath = $file['tmp_name'] ?? '';
        if ($sourcePath === '' || !\file_exists($sourcePath)) {
            throw new Exception(\esc_html__('The uploaded CSV file is missing.', 'reviewx'));
        }
        $uploadDir = \wp_upload_dir();
        $targetDir = \trailingslashit($uploadDir['basedir']) . 'reviewx/imports';
        if (!\wp_mkdir_p($targetDir)) {
            throw new Exception(\esc_html__('Unable to prepare the import staging directory.', 'reviewx'));
        }
        $originalName = \sanitize_file_name((string) ($file['name'] ?? 'reviews.csv'));
        $stagedFilePath = \trailingslashit($targetDir) . \wp_unique_filename($targetDir, $originalName !== '' ? $originalName : 'reviews.csv');
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        if (!empty($wp_filesystem) && $wp_filesystem->move($sourcePath, $stagedFilePath, \true)) {
            return $stagedFilePath;
        }
        if (@\copy($sourcePath, $stagedFilePath)) {
            \wp_delete_file($sourcePath);
            return $stagedFilePath;
        }
        throw new Exception(\esc_html__('Unable to stage the uploaded CSV file.', 'reviewx'));
    }
    protected function scheduleNextImportBatch(int $userId) : void
    {
        // Kept for backwards compatibility with older scheduled-import flows.
    }
    protected function acquireImportProcessLock(int $userId) : bool
    {
        $lockKey = $this->getImportProcessLockKey($userId);
        if (\get_transient($lockKey)) {
            return \false;
        }
        \set_transient($lockKey, 1, self::IMPORT_PROCESS_LOCK_TTL);
        return \true;
    }
    protected function releaseImportProcessLock(int $userId) : void
    {
        \delete_transient($this->getImportProcessLockKey($userId));
    }
    protected function getImportProcessLockKey(?int $userId = null) : string
    {
        $userId = $this->resolveImportUserId($userId);
        return self::IMPORT_PROCESS_LOCK_PREFIX . ($userId > 0 ? $userId : 'guest');
    }
    protected function finalizeImportState(array $state, int $userId) : void
    {
        $this->logImportTrace('Finalizing staged import state.', 'debug', ['user_id' => $userId, 'total_rows' => (int) ($state['total_rows'] ?? 0), 'success_rows' => (int) ($state['success_rows'] ?? 0), 'failed_rows' => (int) ($state['failed_rows'] ?? 0), 'duplicate_rows' => (int) ($state['duplicate_rows'] ?? 0)]);
        $this->refreshImportedPostAggregates($state);
        $totalRows = (int) ($state['total_rows'] ?? 0);
        $successRows = (int) ($state['success_rows'] ?? 0);
        $failedRows = (int) ($state['failed_rows'] ?? 0);
        $duplicateRows = (int) ($state['duplicate_rows'] ?? 0);
        $replySuccessRows = (int) ($state['reply_success_rows'] ?? 0);
        $replyFailedRows = (int) ($state['reply_failed_rows'] ?? 0);
        $this->setImportProgress(['is_importing' => \true, 'is_complete' => \false, 'stage' => 'syncing', 'total_rows' => $totalRows, 'processed_rows' => $successRows + $failedRows + $duplicateRows, 'success_rows' => $successRows, 'failed_rows' => $failedRows, 'duplicate_rows' => $duplicateRows, 'reply_rows' => $replySuccessRows + $replyFailedRows, 'reply_success_rows' => $replySuccessRows, 'reply_failed_rows' => $replyFailedRows, 'percentage' => 100, 'message' => \__('Review import completed. Starting the regular ReviewX sync process...', 'reviewx')], $userId);
        $syncSuccess = $this->dataSyncService->dataSync('import', 'product');
        $this->logImportHistoryToSaas($state);
        $this->cacheServices->removeCache();
        $this->loginService->resetPostMeta();
        \delete_transient('rvx_admin_aggregation');
        $this->reviewDuplicateService->clearDuplicateReviewScanCache();
        $this->setImportProgress(['is_importing' => \false, 'is_complete' => \true, 'stage' => $syncSuccess ? 'completed' : 'warning', 'total_rows' => $totalRows, 'processed_rows' => $successRows + $failedRows + $duplicateRows, 'success_rows' => $successRows, 'failed_rows' => $failedRows, 'duplicate_rows' => $duplicateRows, 'reply_rows' => $replySuccessRows + $replyFailedRows, 'reply_success_rows' => $replySuccessRows, 'reply_failed_rows' => $replyFailedRows, 'percentage' => 100, 'message' => $syncSuccess ? \__('Review import completed and synced successfully.', 'reviewx') : \__('Review import completed, but starting the regular ReviewX sync process failed.', 'reviewx'), 'completed_at' => \time()], $userId);
        if (!empty($state['file_path']) && \file_exists((string) $state['file_path'])) {
            \wp_delete_file((string) $state['file_path']);
        }
        $this->deleteImportState($userId);
    }
    protected function refreshImportedPostAggregates(array $state) : void
    {
        $postIds = \array_values(\array_unique(\array_filter(\array_map('intval', $state['affected_post_ids'] ?? []))));
        $this->logImportTrace('Refreshing imported post aggregates.', 'debug', ['affected_post_ids' => $postIds]);
        foreach ($postIds as $postId) {
            \ReviewX\CPT\CptAverageRating::update_average_rating($postId);
        }
    }
    protected function logImportHistoryToSaas(array $state) : void
    {
        try {
            $historyPayload = ['uid' => Client::getUid(), 'name' => $state['file_name'] ?? 'reviews.csv', 'map' => $state['map'] ?? [], 'wp_review_ids' => \array_values(\array_unique(\array_map('intval', $state['wp_review_ids'] ?? []))), 'stats' => ['total_reviews' => (int) ($state['total_rows'] ?? 0), 'success_reviews' => (int) ($state['success_rows'] ?? 0), 'failed_reviews' => (int) ($state['failed_rows'] ?? 0), 'duplicate_reviews' => (int) ($state['duplicate_rows'] ?? 0), 'reply_reviews' => (int) ($state['reply_success_rows'] ?? 0) + (int) ($state['reply_failed_rows'] ?? 0), 'reply_success_reviews' => (int) ($state['reply_success_rows'] ?? 0), 'reply_failed_reviews' => (int) ($state['reply_failed_rows'] ?? 0)]];
            $this->logImportTrace('Logging import history to SaaS.', 'debug', $historyPayload);
            $logResponse = (new ReviewImportAndExportApi())->logImportHistory($historyPayload);
            $responseStatus = $logResponse->getStatusCode();
            $responseBody = $logResponse->getBody();
            if ($responseStatus >= 400) {
                $this->logImportTrace('SaaS logImportHistory returned an error status.', 'error', ['status' => $responseStatus, 'body' => $responseBody]);
            } else {
                $this->logImportTrace('SaaS logImportHistory completed successfully.', 'debug', ['status' => $responseStatus, 'body' => $responseBody]);
            }
        } catch (\Throwable $e) {
            $this->logImportTrace('SaaS logImportHistory failed.', 'error', ['error' => $e->getMessage()]);
        }
    }
}
