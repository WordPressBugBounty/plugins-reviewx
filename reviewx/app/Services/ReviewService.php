<?php

namespace Rvx\Services;

use Exception;
use Rvx\WPDrill\Response;
use Rvx\Enum\ReviewStatusEnum;
use Rvx\Models\User;
use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
class ReviewService extends \Rvx\Services\Service
{
    protected ReviewsApi $reviewApi;
    public function __construct()
    {
        $this->reviewApi = new ReviewsApi();
    }
    public function getReviews($data)
    {
        $query = \http_build_query($data);
        return (new ReviewsApi())->getReviews($query);
    }
    public function reviewList($data)
    {
        $query = \http_build_query($data);
        return (new ReviewsApi())->reviewList($query);
    }
    public function createReview($request)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        try {
            $wpCommentData = $this->prepareWpCommentData($request);
            $commentId = $this->storeReviewMeta($request, $wpCommentData);
            $appReviewData = $this->prepareAppReviewData($request->get_params(), $commentId);
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->create($appReviewData);
            if ($res->getStatusCode() !== Response::HTTP_OK) {
                wp_delete_comment($commentId, \true);
                return $res;
            }
            $wpdb->query('COMMIT');
            return $res;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }
    /**
     * Store review metadata.
     *
     * @param array $request The request data.
     * @param array $wpCommentData The prepared WordPress comment data.
     * @return int The inserted comment ID.
     */
    public function storeReviewMeta($data, array $wpCommentData) : int
    {
        try {
            $files = $data->get_file_params();
            $max_file_size = 100 * 1024 * 1024;
            $attachments = [];
            $uploaded_images = isset($files['attachments']) ? $files['attachments'] : [];
            if ($uploaded_images) {
                foreach ($uploaded_images['size'] as $size) {
                    if ($size > $max_file_size) {
                        throw new Exception('File size exceeds 100MB limit');
                    }
                }
                $attachments = $this->fileUpload($uploaded_images);
            } else {
                $attachments = $data->get_params()['attachments'] ? $data->get_params()['attachments'] : [];
            }
            $criterias = isset($data->get_params()['criterias']) ? $data->get_params()['criterias'] : null;
            // Calculate the average rating
            $averageRating = $this->calculateAverageRating($criterias);
            $commentId = wp_insert_comment($wpCommentData);
            if (!is_wp_error($commentId)) {
                add_comment_meta($commentId, 'reviewx_title', \strip_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null));
                add_comment_meta($commentId, 'is_recommended', \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0);
                add_comment_meta($commentId, 'verified', \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified']);
                add_comment_meta($commentId, 'is_anonymous', \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0);
                add_comment_meta($commentId, 'rvx_criterias', $criterias);
                add_comment_meta($commentId, 'rating', $averageRating);
                add_comment_meta($commentId, 'reviewx_attachments', $attachments);
                add_comment_meta($commentId, 'rvx_review_version', 'v2');
                return $commentId;
            }
            return 0;
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
    public function storeReviewMetaFormWidget($data, array $wpCommentData, $file = []) : int
    {
        try {
            $attachments = !empty($file) ? $file : [];
            $criterias = isset($data->get_params()['criterias']) ? $data->get_params()['criterias'] : null;
            // Calculate the average rating
            $averageRating = $this->calculateAverageRating($criterias);
            $commentId = wp_insert_comment($wpCommentData);
            if (!is_wp_error($commentId)) {
                add_comment_meta($commentId, 'reviewx_title', \strip_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null));
                add_comment_meta($commentId, 'is_recommended', \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0);
                add_comment_meta($commentId, 'verified', \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified']);
                add_comment_meta($commentId, 'is_anonymous', \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0);
                add_comment_meta($commentId, 'rvx_criterias', $criterias);
                add_comment_meta($commentId, 'rating', $averageRating);
                add_comment_meta($commentId, 'reviewx_attachments', $attachments);
                add_comment_meta($commentId, 'rvx_review_version', 'v2');
                return $commentId;
            }
            return 0;
        } catch (Exception $e) {
            \error_log($e->getMessage());
        }
    }
    public function prepareWpCommentData($request) : array
    {
        $data = $this->getReviewStatus();
        $auto_approve_reviews = $data['setting']['review_settings']['reviews']['auto_approve_reviews'];
        $review_type = get_post_type($request['wp_post_id']) === 'product' ? 'review' : 'comment';
        return ['comment_post_ID' => absint($request['wp_post_id']), 'comment_content' => \strip_tags(\trim($request['feedback'], '"') ?? null), 'comment_author' => sanitize_text_field($request['reviewer_name']), 'comment_author_email' => sanitize_text_field($request['reviewer_email']), 'comment_type' => $review_type, 'comment_approved' => $auto_approve_reviews === \true ? 1 : 0, 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'user_id' => sanitize_text_field($request['user_id']) ?? 0, 'comment_date' => current_time('mysql', \true)];
    }
    public function getReviewStatus()
    {
        return get_option('_rvx_settings_data');
    }
    /**
     * Prepare application review data.
     *
     * @param array $request The request data.
     * @param int $commentId The comment ID.
     * @return array The prepared application review data.
     */
    public function prepareAppReviewData(array $payloadData, int $commentId) : array
    {
        $isVerified = isset($payloadData['verified']) == 1 ? \true : \false;
        $autoPublish = isset($payloadData['auto_publish']) == 1 ? \true : \false;
        $data = ['wp_id' => $commentId, 'product_wp_unique_id' => Client::getUid() . '-' . $payloadData['wp_post_id'] ?? null, 'wp_post_id' => $payloadData['wp_post_id'] ?? null, 'reviewer_email' => sanitize_text_field($payloadData['reviewer_email'] ?? null), 'reviewer_name' => sanitize_text_field($payloadData['reviewer_name'] ?? null), 'rating' => (int) sanitize_text_field($payloadData['rating'] ?? 0), 'feedback' => \strip_tags($payloadData['feedback'] ?? null), 'is_verified' => $isVerified, 'auto_publish' => $autoPublish, 'created_at' => current_time('mysql', \true), 'title' => \strip_tags($payloadData['title'] ?? null), 'attachments' => isset($payloadData['attachments']) ? $payloadData['attachments'] : []];
        $data = \array_merge($data, $payloadData);
        $criterias = Helper::arrayGet($data, 'criterias');
        if ($criterias) {
            $data['criterias'] = \array_map('intval', $criterias);
        }
        return $data;
    }
    public function aiReviewCount()
    {
        return $this->reviewApi->aiReviewCount();
    }
    public function fileUpload($uploaded_images)
    {
        if (!isset($uploaded_images['name'])) {
            return;
        }
        $uploaded_urls = [];
        foreach ($uploaded_images['name'] as $key => $image_name) {
            $file = ['name' => $uploaded_images['name'][$key], 'type' => $uploaded_images['type'][$key], 'tmp_name' => $uploaded_images['tmp_name'][$key], 'error' => $uploaded_images['error'][$key], 'size' => $uploaded_images['size'][$key]];
            $upload = wp_handle_upload($file, ['test_form' => \false]);
            if (isset($upload['url'])) {
                $attachment_id = wp_insert_attachment(['guid' => $upload['url'], 'post_mime_type' => $upload['type'], 'post_title' => sanitize_file_name($file['name']), 'post_content' => '', 'post_status' => 'publish'], $upload['file']);
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                $uploaded_urls[] = wp_get_attachment_url($attachment_id);
            }
        }
        return $uploaded_urls;
    }
    public function deleteReview($request)
    {
        $wpUniqueId = sanitize_text_field($request->get_param('wpUniqueId'));
        $this->reviewDelete($request->get_params());
        $delete_rev = (new ReviewsApi())->deleteReviewData($wpUniqueId);
        if ($delete_rev) {
            return Helper::rest($delete_rev)->success();
        }
        return Helper::rest(null)->fails(__("Review Delete Fails", "reviewx"));
    }
    public function reviewDelete($data)
    {
        $parts = \explode('-', $data['wpUniqueId']);
        $last_part = \end($parts);
        wp_delete_comment($last_part, \true);
    }
    public function restoreReview($request)
    {
        $wpUniqueId = sanitize_text_field($request->get_param('wpUniqueId'));
        $response = (new ReviewsApi())->restoreReview($wpUniqueId);
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->restoreTrashToPublish($wpUniqueId);
        }
        return $response;
    }
    public function restoreTrashToPublish($review_unique_id)
    {
        $id = $this->getLastSegment($review_unique_id);
        $status = get_comment_meta($id, '_wp_trash_meta_status', \true);
        if ($status) {
            wp_set_comment_status($id, 'approve');
        } else {
            wp_set_comment_status($id, 'hold');
        }
    }
    public function getReview($request)
    {
        $wpUniqueId = sanitize_text_field($request->get_param('wpUniqueId'));
        return (new ReviewsApi())->getReview($wpUniqueId);
    }
    public function restoreTrashItem($data)
    {
        //Bulk trash restore
        $this->bulkRestoreTrashItem($data);
        return (new ReviewsApi())->restoreTrashItem($data);
    }
    public function bulkRestoreTrashItem($data)
    {
        foreach ($data['wp_id'] as $id) {
            $status = get_comment_meta($id, '_wp_trash_meta_status', \true);
            if ($status === ReviewStatusEnum::APPROVED) {
                wp_set_comment_status($id, 'approve');
            } else {
                wp_set_comment_status($id, 'hold');
            }
        }
    }
    public function isVerify($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $status = $request->get_param('status');
        $verifyData = (new ReviewsApi())->verifyReview($status, $wpUniqueId);
        if ($verifyData) {
            return Helper::rest($verifyData()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(__('Verify Fail', 'reviewx'));
    }
    public function isvisibility($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $statusData = ['status' => $request->get_param('status')];
        $this->visibilitySpam($request->get_params());
        return (new ReviewsApi())->visibilityReviewData($statusData, $wpUniqueId);
    }
    public function visibilitySpam($data)
    {
        if (ReviewStatusEnum::SPAM === $data['status']) {
            wp_spam_comment($data['wp_id']);
        }
        if (ReviewStatusEnum::APPROVED === $data['status']) {
            wp_set_comment_status($data['wp_id'], 'approve');
        }
        if (ReviewStatusEnum::PENDING === $data['status']) {
            wp_set_comment_status($data['wp_id'], 'hold');
        }
        if (ReviewStatusEnum::TRASH === $data['status']) {
            wp_trash_comment($data['wp_id']);
        }
        return \true;
    }
    public function updateReqEmail($request)
    {
        $data = [];
        $wpUniqueId = $request['wpUniqueId'];
        $verifyData = (new ReviewsApi())->sendUpdateReviewRequestEmail($data, $wpUniqueId);
        if ($verifyData) {
            return Helper::rest($verifyData()->from('data')->toArray())->success(__("Varify", "reviewx"));
        }
        return Helper::rest(null)->fails(__('Fail', 'reviewx'));
    }
    public function reviewReplies($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $replies = ['reply' => $request['reply'], 'wp_id' => $this->getLastSegment($wpUniqueId)];
        $commentReply = (new ReviewsApi())->commentReply($replies, $wpUniqueId);
        if ($commentReply) {
            $this->reviewRepliesForWp($replies);
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(__('Replies Fail', 'reviewx'));
    }
    public function reviewRepliesForWp($replies)
    {
        $parentReviewId = $replies['wp_id'];
        $parent_comment = get_comment($parentReviewId);
        if (!$parent_comment) {
            return \false;
        }
        $replyData = $this->prepareDataForReply($parent_comment, $replies, $parentReviewId);
        if ($parent_comment->comment_parent == 0) {
            $replayId = wp_insert_comment($replyData);
        }
    }
    public function prepareDataForReply($parent_comment, $replies, $parentReviewId)
    {
        return ['comment_post_ID' => $parent_comment->comment_post_ID, 'comment_author' => $parent_comment->comment_author, 'comment_author_email' => $parent_comment->comment_author_email, 'comment_author_url' => '', 'comment_content' => $replies['reply'], 'comment_type' => 'review', 'comment_parent' => $parentReviewId, 'user_id' => get_current_user_id(), 'comment_approved' => 1, 'comment_date' => current_time('mysql'), 'comment_date_gmt' => current_time('mysql', 1)];
    }
    private function getLastSegment($string)
    {
        $lastHyphenPos = \strrpos($string, '-');
        if ($lastHyphenPos === \false) {
            return $string;
        }
        return \substr($string, $lastHyphenPos + 1);
    }
    public function reviewRepliesUpdate($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $repliesUpdate = ['reply' => $request['reply']];
        $commentReply = (new ReviewsApi())->updateCommentReply($repliesUpdate, $wpUniqueId);
        if ($commentReply) {
            $this->reviewRepliesUpdateForWp($wpUniqueId, $repliesUpdate);
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(__('Update Fail', 'reviewx'));
    }
    private function reviewRepliesUpdateForWp($wpUniqueId, $repliesUpdate)
    {
        $parentReviewId = $this->getLastSegment($wpUniqueId);
        $comment_data = array('comment_ID' => $parentReviewId, 'comment_content' => $repliesUpdate['reply'], 'comment_date' => current_time('mysql'), 'comment_date_gmt' => current_time('mysql', 1));
        $updated_comment_id = wp_update_comment($comment_data);
    }
    public function reviewRepliesDelete($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $commentReply = (new ReviewsApi())->deleteCommentReply($wpUniqueId);
        if ($commentReply) {
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(__('Delete Fail', 'reviewx'));
    }
    public function aiReview($request)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        try {
            $comment_id = wp_insert_comment($this->aiReviewWp($request));
            $reviApp = $this->aiReviewApp($request, $comment_id);
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->aiReview($reviApp);
            $resReviewData = $res->getApiData();
            wp_update_comment($comment_id, $resReviewData);
            $wpdb->query('COMMIT');
            return $res;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }
    public function aggregationMeta($request)
    {
        try {
            foreach ($request->get_params() as $data) {
                if (Helper::arrayGet($data, 'product_wp_id')) {
                    return;
                }
                update_post_meta($data['product_wp_id'], '_rvx_latest_reviews_insight', \json_encode($data['meta']));
            }
            return Helper::rest()->success("Success");
        } catch (Exception $e) {
            return Helper::rest($e->getMessage())->fails("Fails");
        }
    }
    public function aiReviewApp($request, $comment_id)
    {
        return ["wp_id" => $comment_id, "product_wp_unique_id" => $request['product_wp_unique_id'], "wp_post_id" => $request['wp_post_id'], "max_reviews" => $request['max_reviews'], "status" => $request['status'], "verified" => $request['verified'], "region" => $request['region'], "religious" => $request['religious'], "gender" => $request['gender']];
    }
    public function aiReviewWp($request)
    {
        return ['comment_post_ID' => absint($request['product_id']), 'comment_content' => sanitize_text_field($request['feedback'] ?? ''), 'comment_author' => sanitize_text_field(get_userdata(get_current_user_id())->display_name), 'comment_author_email' => sanitize_text_field(get_userdata(get_current_user_id())->user_email), 'comment_type' => 'review', 'comment_approved' => sanitize_text_field($request['status'] ?? ''), 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'comment_date' => current_time('mysql', \true)];
    }
    public function updateWooReview($updatedData, $wpUpdatedData)
    {
        //$reviewId = $updatedData['wp_id'];
        $wpUniqueId = $updatedData['wp_unique_id'];
        try {
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->updateWooReviewData($updatedData, $wpUniqueId);
            if ($res->getStatusCode() !== Response::HTTP_OK) {
                return ['error' => $res->getStatusCode()];
            }
            return $res;
        } catch (Exception $e) {
            return ["error" => "Review Not updated"];
        }
    }
    public function updateReview($request)
    {
        $reviewId = $request['wp_id'];
        $wpUniqueId = $request['wpUniqueId'];
        try {
            $existingReview = get_comment($reviewId);
            $wpCommentData = $this->prepareUpdateWpComment($request, $existingReview);
            $appReviewData = $this->prepareUpdateAppReview($request->get_params(), $request->get_file_params());
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->updateReviewData($appReviewData, $wpUniqueId);
            if ($res->getStatusCode() !== Response::HTTP_OK) {
                return ['error' => $res->getStatusCode()];
            }
            wp_update_comment(['comment_ID' => $reviewId, 'comment_content' => \strip_tags($wpCommentData['comment_content']), 'comment_approved' => sanitize_text_field($wpCommentData['comment_approved']), 'comment_author_email' => sanitize_text_field($wpCommentData['comment_author_email']), 'comment_author' => sanitize_text_field($wpCommentData['comment_author'])]);
            $this->updateReviewMeta($reviewId, $request);
            return $res;
        } catch (Exception $e) {
            return ["error" => "Review Not updated"];
        }
    }
    public function updateReviewMeta($reviewId, $data)
    {
        $attachments = \array_key_exists('attachements', $data->get_params()) ? $data->get_params()['attachements'] : [];
        $criterias = \array_key_exists('criterias', $data->get_params()) ? $data->get_params()['criterias'] : null;
        // Calculate the average rating
        $averageRating = $this->calculateAverageRating($criterias);
        // Sanitize and update review meta fields
        update_comment_meta($reviewId, 'reviewx_title', sanitize_text_field(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null));
        update_comment_meta($reviewId, 'verified', \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified']);
        update_comment_meta($reviewId, 'is_recommended', \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0);
        update_comment_meta($reviewId, 'is_anonymous', \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0);
        update_comment_meta($reviewId, 'rvx_criterias', $criterias);
        update_comment_meta($reviewId, 'rating', $averageRating);
        update_comment_meta($reviewId, 'reviewx_attachments', $attachments);
    }
    /**
     * Prepare WordPress comment data for updating.
     *
     * @param array $request The request data.
     * @param WP_Comment $existingReview The existing review data.
     * @return array The prepared WordPress comment data.
     */
    public function prepareUpdateWpComment($request, $existingReview)
    {
        return ['comment_content' => \strip_tags($request['feedback'] ?? $existingReview->comment_content), 'comment_approved' => sanitize_text_field($request['status'] ?? $existingReview->comment_approved), 'comment_author_email' => sanitize_text_field($request['reviewer_email'] ?? $existingReview->comment_author_email), 'comment_author' => sanitize_text_field($request['reviewer_name'] ?? $existingReview->comment_author)];
    }
    public function prepareUpdateAppReview(array $payloadData, array $files)
    {
        $isRecommended = isset($payloadData['is_recommended']) == 1 ? \true : \false;
        // $uploaded_images = $files['attachments'];
        $uploaded_images = isset($files['attachments']) ? $files['attachments'] : [];
        $attachments = $this->fileUpload($uploaded_images);
        $rating = Helper::arrayGet($payloadData, 'rating', null);
        $payloadData['rating'] = $rating ? (int) $rating : null;
        $data = [
            // 'rating' => (int)$payloadData['rating'],
            'feedback' => \strip_tags($payloadData['feedback']),
            'title' => \strip_tags($payloadData['title']),
            'reviewer_name' => sanitize_text_field($payloadData['reviewer_name']),
            'reviewer_email' => sanitize_text_field($payloadData['reviewer_email']),
            'date' => current_time('mysql', \true),
            'anonymous' => isset($payloadData['anonymous']),
            'is_recommended' => $isRecommended,
            'attachment_access' => isset($payloadData['attachment_access']),
        ];
        $data = \array_merge($payloadData, $data);
        $criterias = Helper::arrayGet($payloadData, 'criterias');
        if ($criterias) {
            $payloadData['criterias'] = \array_map('intval', $criterias);
        }
        return $data;
    }
    public function getWidgetReviewsForProduct($request)
    {
        return (new ReviewsApi())->getWidgetReviewsForProductApi($request);
    }
    public function settingMeta($request)
    {
        try {
            $settings = $request->get_params()['meta'];
            update_option('_rvx_settings_data', $settings);
            return Helper::rest()->success("Success");
        } catch (Exception $th) {
            return Helper::rest()->fails("Fails");
        }
    }
    public static function getSpecificReviewItem($data)
    {
        $review_ids = $data['review_ids'];
        $uid = Client::getUid();
        $review_wp_unique_ids = \array_map(function ($id) use($uid) {
            return $uid . '-' . $id;
        }, $review_ids);
        $data = [];
        $data['review_wp_unique_ids'] = $review_wp_unique_ids;
        return (new ReviewsApi())->getSpecificReviewItem($uid, $data);
    }
    public function getSingleProductAllReviews($data)
    {
        return (new ReviewsApi())->getSingleProductAllReviews($data);
    }
    public function getWidgetInsight($request)
    {
        return (new ReviewsApi())->getWidgetInsight($request);
    }
    public function getWidgetInsightForWp($request, $aggregation)
    {
        update_post_meta($request['product_id'], 'rvx_aggregation_insight', $aggregation);
    }
    public function reviewBulkUpdate($data)
    {
        $this->reviewBulkStatusUpdateForWp($data);
        return (new ReviewsApi())->reviewBulkUpdate($data);
    }
    public function reviewBulkStatusUpdateForWp($data)
    {
        if (ReviewStatusEnum::SPAM === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                wp_spam_comment($id);
            }
        }
        if (ReviewStatusEnum::APPROVED === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                wp_set_comment_status($id, 'approve');
            }
        }
        if (ReviewStatusEnum::PENDING === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                wp_set_comment_status($id, 'hold');
            }
        }
        if (ReviewStatusEnum::TRASH === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                wp_trash_comment($id);
            }
        }
    }
    public function reviewBulkTrash($data)
    {
        $this->bulkTrashInWp($data);
        return (new ReviewsApi())->reviewBulkTrash($data);
    }
    public function bulkTrashInWp($data)
    {
        if (!\is_array($data)) {
            return \false;
        }
        foreach ($data['wp_id'] as $review_id) {
            wp_trash_comment($review_id, \true);
        }
    }
    public function reviewEmptyTrash($data)
    {
        $this->emptyTrashInWp($data);
        return (new ReviewsApi())->reviewEmptyTrash();
    }
    public function emptyTrashInWp($review_ids)
    {
        if (!\is_array($review_ids)) {
            return \false;
        }
        foreach ($review_ids['wp_ids'] as $review_id) {
            wp_delete_comment($review_id, \true);
        }
        return \true;
    }
    public function reviewAggregation()
    {
        return (new ReviewsApi())->reviewAggregation();
    }
    public function saveWidgetReviewsForProduct($request)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        try {
            $data = $this->dataMarge($request);
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->saveWidgetReviewsForProductApi($data);
            $wpdb->query('COMMIT');
            if ($res->getStatusCode() !== Response::HTTP_OK) {
                wp_delete_comment($data['wp_id'], \true);
                return $res;
            }
            return $res;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }
    public function dataMarge($request)
    {
        $files = $request->get_file_params();
        $max_file_size = 100 * 1024 * 1024;
        // 100MB in bytes
        $attachments = [];
        if (!empty($files['attachments']['name']) && \is_array($files['attachments']['size'])) {
            foreach ($files['attachments']['size'] as $size) {
                if ($size > $max_file_size) {
                    return ['error' => 'File size exceeds 100MB limit'];
                }
            }
            $attachments = $this->fileUpload($files['attachments']);
        }
        $wpCommentData = $this->prepareWpCommentData($request);
        $commentId = $this->storeReviewMetaFormWidget($request, $wpCommentData, $attachments);
        $productId = $request->get_param('wp_post_id');
        $siteUid = Client::getUid();
        $productWpUniqueId = $siteUid . '-' . $productId;
        $data = \array_merge($request->get_params(), ["wp_id" => $commentId, "site_uid" => $siteUid, "product_wp_unique_id" => $productWpUniqueId, "is_anonymous" => $request['is_anonymous'] == "true" ? \true : \false, "is_verified" => $request['verified'] == "true" ? \true : \false, "is_customer_verified" => $request['is_customer_verified'] == "true" ? \true : \false, "attachments" => $attachments, 'created_at' => current_time('mysql', \true), 'is_recommended' => $request['is_recommended'] == "true" ? \true : \false]);
        $data['feedback'] = \strip_tags($request['feedback']);
        $data['title'] = \strip_tags($request['title']);
        $criterias = Helper::arrayGet($data, 'criterias');
        if ($criterias) {
            $data['criterias'] = \array_map('intval', $criterias);
        }
        return $data;
    }
    public function requestReviewEmailAttachment($request)
    {
        $data = $request->get_params();
        foreach ($data as $id) {
            $files = $request->get_file_params();
            $attachments = [];
            $uploaded_images = isset($files['attachments']) ? $files['attachments'] : [];
            $attachments = $this->fileUpload($uploaded_images);
            \error_log("Image print " . \print_r($attachments, \true));
            update_comment_meta($id, 'reviewx_attachments', $attachments);
        }
        $reviews = [];
        // Initialize an empty array to store all reviews
        foreach ($data as $id) {
            $reviews[] = [
                // Append each review to the array
                'wp_id' => $id,
                'attachments' => get_comment_meta($id, 'reviewx_attachments', \true),
            ];
        }
        return Helper::rest($reviews)->success("Success");
    }
    public function reviewMoveToTrash($data)
    {
        $response = (new ReviewsApi())->reviewMoveToTrash($data);
        if ($response->getStatusCode() == Response::HTTP_OK) {
            $this->trashInWp($data);
        }
        return $response;
    }
    public function trashInWp($data)
    {
        $parts = \explode('-', $data['WpUniqueId']);
        $last_part = \end($parts);
        wp_trash_comment($last_part);
    }
    public function likeDIslikePreference($data)
    {
        return (new ReviewsApi())->likeDIslikePreference($data);
    }
    public function reviewListMultiCriteria()
    {
        return (new ReviewsApi())->reviewListMultiCriteria();
    }
    public function highlight($data)
    {
        return (new ReviewsApi())->highlight($data);
    }
    public function bulkTenReviews($data)
    {
        return $data;
    }
    public function reviewRequestStoreItem($data)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        try {
            $comment_ids = $this->commentInsertableFormEmail($data);
            $saasData = $this->prepareAppReviewDataFormEmail($comment_ids);
            //  return $this->reviewApi->reviewRequestStoreItem($saasData, $uid);
            $wpdb->query('COMMIT');
            return $saasData;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }
    public function commentInsertableFormEmail($data)
    {
        $wpCommentData = $this->prepareWpCommentDataForEmail($data);
        return $this->storeReviewMetaForEmail($data, $wpCommentData);
    }
    public function prepareAppReviewDataFormEmail(array $commentIds) : array
    {
        $reviewData = ['reviews' => []];
        foreach ($commentIds as $commentId) {
            $comment = get_comment($commentId);
            if (!$comment) {
                continue;
            }
            $data = ['wp_id' => (int) $comment->comment_ID, 'product_wp_unique_id' => Client::getUid() . '-' . $comment->comment_post_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'reviewer_email' => $comment->comment_author_email, 'reviewer_name' => $comment->comment_author, 'rating' => (int) get_comment_meta($comment->comment_ID, 'rating', \true), 'feedback' => $comment->comment_content, 'created_at' => $comment->comment_date, 'title' => get_comment_meta($comment->comment_ID, 'rvx_comment_title', \true), 'order_item_wp_unique_id' => get_comment_meta($comment->comment_ID, 'rvx_comment_order_item', \true)];
            $reviewData['reviews'][] = $data;
        }
        return $reviewData;
    }
    public function prepareWpCommentDataForEmail($data) : array
    {
        $settingsData = $this->getReviewStatus();
        $auto_approve_reviews = $settingsData['setting']['review_settings']['reviews']['auto_approve_reviews'];
        $dataStore = [];
        foreach ($data['reviews'] as $review) {
            $dataWp = ['comment_post_ID' => absint($review['product_wp_id']), 'comment_content' => sanitize_text_field($review['feedback'] ?? ''), 'comment_author' => sanitize_text_field($review['reviewer_name']), 'comment_author_email' => sanitize_text_field($review['reviewer_email']), 'comment_type' => 'review', 'comment_approved' => $auto_approve_reviews === \true ? 1 : 0, 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'user_id' => absint($review['user_id']) ?? 0, 'comment_date' => current_time('mysql', \true)];
            $dataStore[] = $dataWp;
        }
        return $dataStore;
    }
    public function storeReviewMetaForEmail($data, array $wpCommentData) : array
    {
        try {
            $id = [];
            foreach ($wpCommentData as $index => $comment) {
                $criterias = $data['reviews'][$index]['criterias'];
                if (!empty($criterias)) {
                    // Calculate the average rating
                    $averageRating = $this->calculateAverageRating($criterias);
                    $modified_criteria = \json_encode($criterias);
                } else {
                    $averageRating = sanitize_text_field($data['reviews'][$index]['rating']) ?? 1;
                    $modified_criteria = null;
                }
                $commentId = wp_insert_comment($comment);
                add_comment_meta($commentId, 'rvx_comment_title', sanitize_text_field($data['reviews'][$index]['title'] ?? null));
                add_comment_meta($commentId, 'rvx_criterias', $modified_criteria);
                add_comment_meta($commentId, 'rating', $averageRating);
                add_comment_meta($commentId, 'rvx_comment_order_item', sanitize_text_field($data['reviews'][$index]['order_item_wp_unique_id']));
                add_comment_meta($commentId, 'verified', 1);
                add_comment_meta($commentId, 'is_recommended', 1);
                add_comment_meta($commentId, 'reviewx_attachments', []);
                add_comment_meta($commentId, 'rvx_review_version', 'v2');
                $id[] = $commentId;
            }
            return $id;
        } catch (Exception $e) {
            throw new Exception('Something went wrong!' . $e->getMessage());
        }
    }
    /**
     * Calculate the average rating from criteria values.
     *
     * @param array|null $criterias An array containing numeric values or key-value pairs with numeric values.
     * @return int The ceiling value of the average, or 1 if no valid data exists.
     */
    public function calculateAverageRating($criterias)
    {
        // Ensure $criterias is an array; default to an empty array if it's null or not an array
        if (!\is_array($criterias)) {
            return 1;
            // Fallback value
        }
        // Normalize all values to integers, ensuring they are numeric
        $values = \array_map(function ($value) {
            return \is_numeric($value) ? (int) $value : 0;
            // Convert numeric values to integers, default non-numeric to 0
        }, \array_values($criterias));
        // Filter out any invalid (zero or negative) values
        $values = \array_filter($values, function ($value) {
            return $value > 0;
            // Keep only positive integers
        });
        $total = \array_sum($values);
        // Sum of all valid values
        $count = \count($values);
        // Count of valid values
        return $count > 0 ? \ceil($total / $count) : 1;
        // Calculate the average and round up
    }
    public function thanksMessage($request)
    {
        return ['message' => "Thank you for sharing your review"];
    }
    public function postMetaReviewInsert($id, $latest_reviews)
    {
        update_post_meta($id, "_rvx_latest_reviews", wp_slash($latest_reviews));
    }
}
