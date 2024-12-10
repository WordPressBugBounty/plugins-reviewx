<?php

namespace Rvx\Services;

use Exception;
use Rvx\Api\ReviewImportAndExportApi;
class ImportExportServices extends \Rvx\Services\Service
{
    /**
     */
    public function importSupportedAppStore($data)
    {
        return (new ReviewImportAndExportApi())->importSupportedAppStore($data);
    }
    /**
     */
    public function importStore($request)
    {
        $files = $request->get_file_params();
        $data = $request->get_params();
        $this->importReviewStore($files, $data);
        return (new ReviewImportAndExportApi())->importStore($data, $files);
    }
    public function importReviewStore($files, $data)
    {
        $reviews = [];
        if (($handle = \fopen($files['file']['tmp_name'], 'r')) !== \FALSE) {
            // Get the header row
            $header = \fgetcsv($handle);
            while (($data = \fgetcsv($handle)) !== \FALSE) {
                $reviews[] = \array_combine($header, $data);
            }
            \fclose($handle);
        }
        $this->prepareImportDataReview($reviews);
        return $reviews;
    }
    public function productIdGet($product_name)
    {
        $args = array('post_type' => 'product', 'posts_per_page' => 1, 's' => $product_name);
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }
        return null;
    }
    public function prepareImportDataReview($reviews)
    {
        foreach ($reviews as $review_data) {
            $reviews_id = $this->productIdGet($review_data['Product_Title']);
            if ($reviews_id) {
                $this->insertReview($reviews_id, $review_data);
            }
        }
    }
    public function insertReview($reviews_id, $review_data)
    {
        $comment_data = ['comment_post_ID' => $reviews_id, 'comment_author' => $review_data['Reviewer_Name'], 'comment_author_email' => $review_data['Email'], 'comment_content' => $review_data['Review_Description'], 'comment_date' => \wp_date('Y-m-d H:i:s', \strtotime($review_data['Date (YYYY-MM-DD H:M)'])), 'comment_approved' => 1, 'comment_type' => 'review', 'comment_meta' => ['rating' => $review_data['Rating'], 'media' => $review_data['Media']]];
        $comment_id = wp_insert_comment($comment_data);
        if ($comment_id) {
            add_comment_meta($comment_id, 'rating', $review_data['Rating']);
            add_comment_meta($comment_id, 'attachments', $review_data['Media']);
        }
    }
    public function processedFile($file)
    {
        $pathInfo = \pathinfo($file['tmp_name']);
        return ['test' => \false, 'originalName' => $file['name'], 'mimeType' => $file['type'], 'error' => $file['error'], 'path' => \dirname($file['tmp_name']), 'filename' => \basename($file['tmp_name']), 'basename' => $pathInfo['basename'], 'pathname' => $file['tmp_name'], 'realPath' => \realpath($file['tmp_name']), 'type' => 'file', 'file' => \true];
    }
    public function fileUpload($uploaded_image)
    {
        // Handle the file upload
        $upload = wp_handle_upload($uploaded_image, ['test_form' => \false]);
        $attachment_id = wp_insert_attachment(['guid' => $upload['url'], 'post_mime_type' => $upload['type'], 'post_title' => sanitize_file_name($uploaded_image['name']), 'post_content' => '', 'post_status' => 'inherit'], $upload['file']);
        // Generate and update attachment metadata
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        // Return the URL of the uploaded file
        return wp_get_attachment_url($attachment_id);
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
