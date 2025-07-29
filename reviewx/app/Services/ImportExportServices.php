<?php

namespace Rvx\Services;

use Exception;
use Rvx\Api\ReviewImportAndExportApi;
use Rvx\Utilities\Helper;
class ImportExportServices extends \Rvx\Services\Service
{
    public function importSupportedAppStore($data)
    {
        return (new ReviewImportAndExportApi())->importSupportedAppStore($data);
    }
    public function importStore($request)
    {
        $files = $request->get_file_params();
        $data = $request->get_params();
        $this->importReviewStore($files, $data);
        return (new ReviewImportAndExportApi())->importStore($data, $files);
    }
    public function importReviewStore($files, $data)
    {
        $request = $data;
        $reviews = [];
        if (($handle = \fopen($files['file']['tmp_name'], 'r')) !== \FALSE) {
            // Get the header row
            $header = \fgetcsv($handle);
            while (($data = \fgetcsv($handle)) !== \FALSE) {
                $reviews[] = \array_combine($header, $data);
            }
            \fclose($handle);
        }
        $this->prepareImportDataReview($reviews, $request);
        return $reviews;
    }
    public function prepareImportDataReview($reviews, $request)
    {
        if (empty($request['product_match']) || !\is_array($request['product_match'])) {
            return;
        }
        if (empty($reviews) || !\is_array($reviews)) {
            return;
        }
        $productIds = $this->extractProductIds($request['product_match']);
        $this->processReviews($reviews, $productIds, $request);
    }
    /**
     * Extract product IDs from product match data
     */
    private function extractProductIds(array $productMatches) : array
    {
        $productIds = [];
        foreach ($productMatches as $productData) {
            if (\is_array($productData) && isset($productData['product_title'], $productData['wp_id'])) {
                $productIds[] = $productData['wp_id'];
            }
        }
        return $productIds;
    }
    /**
     * Process reviews and insert them
     */
    private function processReviews(array $reviews, array $productIds, array $request) : void
    {
        foreach ($reviews as $index => $reviewData) {
            if (!isset($productIds[$index]) || empty($productIds[$index]) || !\is_array($reviewData)) {
                continue;
            }
            $postType = $reviewData['Post_Type'] ?? 'product';
            try {
                \delete_transient("rvx_{$productIds[$index]}_latest_reviews");
                \delete_transient("rvx_{$productIds[$index]}_latest_reviews_insight");
                $this->insertReview($productIds[$index], $reviewData, $request, $postType);
            } catch (Exception $e) {
                \error_log("Failed to insert review at index {$index}: " . $e->getMessage());
            }
        }
    }
    public function insertReview($reviews_id, $review_data, $request, $post_type)
    {
        $mediaArray = [];
        if (isset($review_data[$request['map']['attachment']]) && !empty($review_data[$request['map']['attachment']])) {
            $mediaArray = \explode(',', $review_data[$request['map']['attachment']]);
        }
        $comment_type = 'review';
        if (!empty($post_type) && \strtolower($post_type) != 'product') {
            $comment_type = 'comment';
        }
        $comment_data = ['comment_post_ID' => $reviews_id, 'comment_author' => $review_data[$request['map']['customer_name']], 'comment_author_email' => $review_data[$request['map']['customer_email']], 'comment_content' => $review_data[$request['map']['feedback']], 'comment_date' => !empty($review_data[$request['map']['created_at']]) && \strtotime($review_data[$request['map']['created_at']]) !== \false ? \wp_date('Y-m-d H:i:s', \strtotime($review_data[$request['map']['created_at']])) : \wp_date('Y-m-d H:i:s'), 'comment_approved' => Helper::arrayGet($request, 'status'), 'comment_type' => $comment_type, 'comment_meta' => ['reviewx_title' => $review_data[$request['map']['review_title']] ?? null, 'rating' => $review_data[$request['map']['rating']] ?? 5, 'reviewx_attachments' => $mediaArray, 'verified' => Helper::arrayGet($request, 'verified')]];
        wp_insert_comment($comment_data);
    }
    /**
     * @throws Exception
     */
    public function importRollback($data)
    {
        return (new ReviewImportAndExportApi())->importRollback($data);
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
}
