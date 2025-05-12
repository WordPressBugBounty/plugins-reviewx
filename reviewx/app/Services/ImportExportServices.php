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
    public function productIdGet($product_name)
    {
        $args = array('post_type' => 'product', 'posts_per_page' => 1, 's' => $product_name);
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }
        return null;
    }
    public function prepareImportDataReview($reviews, $request)
    {
        foreach ($reviews as $review_data) {
            $reviews_id = $this->productIdGet($review_data['Product_Title']);
            if ($reviews_id) {
                $this->insertReview($reviews_id, $review_data, $request);
            }
        }
    }
    public function insertReview($reviews_id, $review_data, $request)
    {
        $mediaArray = \explode(',', Helper::arrayGet($review_data, 'Media'));
        $comment_data = ['comment_post_ID' => $reviews_id, 'comment_author' => $review_data['Reviewer_Name'], 'comment_author_email' => $review_data['Email'], 'comment_content' => $review_data['Review_Description'], 'comment_date' => \wp_date('Y-m-d H:i:s', \strtotime($review_data['Date (YYYY-MM-DD H:M)'])), 'comment_approved' => Helper::arrayGet($request, 'status'), 'comment_type' => 'review', 'comment_meta' => ['rating' => Helper::arrayGet($review_data, 'Rating'), 'attachments' => $mediaArray ?? [], 'verified' => Helper::arrayGet($request, 'verified')]];
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
