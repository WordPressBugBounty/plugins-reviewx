<?php

namespace Rvx\Services;

use Exception;
use Rvx\Api\ReviewsApi;
use Rvx\Enum\ReviewStatusEnum;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\Utilities\TransactionManager;
use Rvx\WPDrill\Response;
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
        return TransactionManager::run(function () use($request) {
            $wpCommentData = $this->prepareWpCommentData($request);
            $commentId = $this->storeReviewMeta($request, $wpCommentData);
            return $commentId;
        }, function ($commentId) use($request) {
            $appReviewData = $this->prepareAppReviewData($request->get_params(), $commentId);
            // Ensure SaaS payload rating equals the exact value stored in WP DB
            $storedRating = get_comment_meta($commentId, 'rating', \true);
            $appReviewData['rating'] = \is_numeric($storedRating) ? (float) \round($storedRating, 2) : (float) 0.0;
            return $this->reviewApi->create($appReviewData);
        });
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
            $isAllowedMultiCriteria = (new \Rvx\Services\SettingService())->getReviewSettings(get_post_type($data->get_params()['wp_post_id']))['reviews']['multicriteria']['enable'] ?? \false;
            // Calculate the average rating
            if ($criterias !== null && $isAllowedMultiCriteria === \true) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
            } else {
                $wcAverageRating = (float) \round($data->get_params()['rating'], 2);
            }
            $wpCommentData['comment_meta'] = ['reviewx_title' => \strip_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null), 'is_recommended' => \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0, 'verified' => \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified'], 'is_anonymous' => \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0, 'rvx_criterias' => $criterias, 'rating' => $wcAverageRating, 'reviewx_attachments' => $attachments, 'rvx_review_version' => 'v2'];
            $commentId = wp_insert_comment($wpCommentData);
            if (!\is_wp_error($commentId) && $commentId > 0) {
                \Rvx\CPT\CptAverageRating::update_average_rating($wpCommentData['comment_post_ID']);
                return $commentId;
            }
            return 0;
        } catch (Exception $e) {
            throw new Exception("Review Save field" . $e->getMessage());
        }
    }
    public function storeReviewMetaFormWidget($data, array $wpCommentData, $file = [])
    {
        try {
            $attachments = !empty($file) ? $file : [];
            $criterias = isset($data->get_params()['criterias']) ? $data->get_params()['criterias'] : null;
            // Calculate the average rating
            $isAllowedMultiCriteria = (new \Rvx\Services\SettingService())->getReviewSettings(get_post_type($data->get_params()['wp_post_id']))['reviews']['multicriteria']['enable'] ?? \false;
            if ($criterias !== null && $isAllowedMultiCriteria === \true) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
            } else {
                $wcAverageRating = \round($data->get_params()['rating'], 2);
            }
            $wpCommentData['comment_meta'] = ['reviewx_title' => \strip_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null), 'is_recommended' => \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0, 'verified' => \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified'], 'is_anonymous' => \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0, 'rvx_criterias' => $criterias, 'rating' => $wcAverageRating, 'reviewx_attachments' => $attachments, 'rvx_review_version' => 'v2'];
            $commentId = wp_insert_comment($wpCommentData);
            if (!\is_wp_error($commentId) && $commentId > 0) {
                \Rvx\CPT\CptAverageRating::update_average_rating($wpCommentData['comment_post_ID']);
                return (int) $commentId;
            }
            return 0;
        } catch (Exception $e) {
            \error_log($e->getMessage());
        }
    }
    public function prepareWpCommentData($request) : array
    {
        $data = (array) (new \Rvx\Services\SettingService())->getReviewSettings(get_post_type($request['wp_post_id']));
        $review_type = 'review';
        if (get_post_type($request['wp_post_id']) !== 'product') {
            $review_type = 'comment';
        }
        $status = Helper::arrayGet($request->get_params(), 'status') ?? $data['reviews']['auto_approve_reviews'];
        if (!$status || $status === 'false' || $status === '0') {
            $status = 0;
        } else {
            $status = 1;
        }
        return ['comment_post_ID' => absint($request['wp_post_id']), 'comment_content' => \strip_tags(\trim($request['feedback'], '"') ?? null), 'comment_author' => sanitize_text_field($request['reviewer_name']), 'comment_author_email' => sanitize_text_field($request['reviewer_email']), 'comment_type' => $review_type, 'comment_approved' => $status, 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'user_id' => sanitize_text_field($request['user_id']) ?? 0, 'comment_date' => current_time('mysql', \true)];
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
        $data = ['wp_id' => $commentId, 'product_wp_unique_id' => Client::getUid() . '-' . $payloadData['wp_post_id'] ?? null, 'wp_post_id' => $payloadData['wp_post_id'] ?? null, 'reviewer_email' => sanitize_text_field($payloadData['reviewer_email'] ?? null), 'reviewer_name' => sanitize_text_field($payloadData['reviewer_name'] ?? null), 'rating' => (float) sanitize_text_field(\round($payloadData['rating'], 2) ?? 0.0), 'feedback' => \strip_tags($payloadData['feedback'] ?? null), 'is_verified' => Helper::arrayGet($payloadData, 'verified'), 'auto_publish' => Helper::arrayGet($payloadData, 'status'), 'created_at' => current_time('mysql', \true), 'title' => \strip_tags($payloadData['title'] ?? null), 'attachments' => isset($payloadData['attachments']) ? $payloadData['attachments'] : []];
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
        return TransactionManager::run(function () use($wpUniqueId) {
            $this->restoreTrashToPublish($wpUniqueId);
            return \true;
        }, function () use($wpUniqueId) {
            return $this->reviewApi->restoreReview($wpUniqueId);
        });
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
        $response = (new ReviewsApi())->restoreTrashItem($data);
        if ((int) $response->getStatusCode() === 200) {
            $this->bulkRestoreTrashItem($data);
        }
        return $response;
    }
    public function bulkRestoreTrashItem($data)
    {
        foreach ($data['wp_id'] as $id) {
            $status = get_comment_meta($id, '_wp_trash_meta_status', \true);
            if ($status == 'approve' || $status == ReviewStatusEnum::APPROVED) {
                wp_set_comment_status($id, 'approve');
            } elseif ($status == 'hold' || $status == ReviewStatusEnum::PENDING) {
                wp_set_comment_status($id, 'hold');
            } elseif ($status == 'unapproved' || $status == ReviewStatusEnum::UNPUBLISHED) {
                wp_set_comment_status($id, 'hold');
            } elseif ($status == 'spam' || $status == ReviewStatusEnum::SPAM) {
                wp_set_comment_status($id, 'spam');
            } else {
                wp_set_comment_status($id, 'approve');
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
        return TransactionManager::run(function () use($request) {
            return $this->visibilitySpam($request->get_params());
        }, function () use($request) {
            $wpUniqueId = $request['wpUniqueId'];
            $statusData = ['status' => $request->get_param('status')];
            return $this->reviewApi->visibilityReviewData($statusData, $wpUniqueId);
        });
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
            return Helper::rest($verifyData()->from('data')->toArray())->success(__("Verify", "reviewx"));
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
            $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            return Helper::rvxApi(['success' => null])->success('Reply submitted sucesfully.', 200);
        }
        return Helper::rest(null)->fails(__('Replies Fail', 'reviewx'));
    }
    public function reviewCacheDelete($review_id)
    {
        $post_id = get_comment($review_id)->comment_post_ID ?? null;
        \delete_transient("rvx_{$post_id}_latest_reviews");
        \delete_transient("rvx_{$post_id}_latest_reviews_insight");
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
            return \true;
        }
    }
    public function prepareDataForReply($parent_comment, $replies, $parentReviewId)
    {
        return ['comment_post_ID' => $parent_comment->comment_post_ID, 'comment_author' => $parent_comment->comment_author, 'comment_author_email' => $parent_comment->comment_author_email, 'comment_author_url' => '', 'comment_content' => $replies['reply'], 'comment_type' => $parent_comment->comment_type, 'comment_parent' => $parentReviewId, 'user_id' => get_current_user_id(), 'comment_approved' => 1, 'comment_date' => current_time('mysql'), 'comment_date_gmt' => current_time('mysql', 1)];
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
            $this->reviewCacheDelete($wpUniqueId);
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(__('Update Fail', 'reviewx'));
    }
    private function reviewRepliesUpdateForWp($wpUniqueId, $repliesUpdate)
    {
        $parentReviewId = $this->getLastSegment($wpUniqueId);
        $comment_data = array('comment_ID' => $parentReviewId, 'comment_content' => $repliesUpdate['reply'], 'comment_date' => current_time('mysql'), 'comment_date_gmt' => current_time('mysql', 1));
        wp_update_comment($comment_data);
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
                $productId = Helper::arrayGet($data, 'product_wp_id');
                if (!$productId) {
                    continue;
                }
                $aggregation_data = \json_encode(wp_slash(Helper::arrayGet($data, "meta")), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
                set_transient("rvx_{$productId}_latest_reviews_insight", $aggregation_data, 604800);
                // Expires in 7 days
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
        $review_type = 'review';
        if (get_post_type($request['wp_post_id']) !== 'product') {
            $review_type = 'comment';
        }
        return ['comment_post_ID' => absint($request['product_id']), 'comment_content' => sanitize_text_field($request['feedback'] ?? ''), 'comment_author' => sanitize_text_field(get_userdata(get_current_user_id())->display_name), 'comment_author_email' => sanitize_text_field(get_userdata(get_current_user_id())->user_email), 'comment_type' => $review_type, 'comment_approved' => sanitize_text_field($request['status'] ?? ''), 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'comment_date' => current_time('mysql', \true)];
    }
    public function updateWooReview($updatedData, $wpUpdatedData)
    {
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
        $reviewId = $request->get_param('wp_id');
        $wpUniqueId = $request->get_param('wpUniqueId');
        return TransactionManager::run(function () use($request, $reviewId) {
            $existingReview = get_comment($reviewId);
            if (!$existingReview) {
                return \false;
            }
            $wpCommentData = $this->prepareUpdateWpComment($request, $existingReview);
            wp_update_comment(['comment_ID' => $reviewId, 'comment_content' => \strip_tags($wpCommentData['comment_content']), 'comment_approved' => sanitize_text_field($wpCommentData['comment_approved']), 'comment_author_email' => sanitize_text_field($wpCommentData['comment_author_email']), 'comment_author' => sanitize_text_field($wpCommentData['comment_author'])]);
            $this->reviewCacheDelete($reviewId);
            $this->updateReviewMeta($reviewId, $request);
            // Update average rating for the post
            \Rvx\CPT\CptAverageRating::update_average_rating($existingReview->comment_post_ID);
            return \true;
        }, function () use($request, $wpUniqueId) {
            $appReviewData = $this->prepareUpdateAppReview($request->get_params(), $request->get_file_params());
            $response = $this->reviewApi->updateReviewData($appReviewData, $wpUniqueId);
            if (!\is_object($response)) {
                return \false;
            }
            return $response;
        });
    }
    public function updateReviewMeta($reviewId, $data)
    {
        $params = $data->get_params();
        // 1. Title
        if (isset($params['title'])) {
            update_comment_meta($reviewId, 'reviewx_title', sanitize_text_field($params['title']));
        }
        // 2. Rating & Criterias
        $criterias = $params['criterias'] ?? null;
        if ($criterias !== null) {
            update_comment_meta($reviewId, 'rvx_criterias', $criterias);
            $wp_post_id = $params['wp_post_id'] ?? get_comment($reviewId)->comment_post_ID;
            $isAllowedMultiCriteria = (new \Rvx\Services\SettingService())->getReviewSettings(get_post_type($wp_post_id))['reviews']['multicriteria']['enable'] ?? \false;
            if ($isAllowedMultiCriteria) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
                update_comment_meta($reviewId, 'rating', $wcAverageRating);
                update_comment_meta($reviewId, 'reviewx_rating', $wcAverageRating);
            }
        } elseif (isset($params['rating'])) {
            $rating = (float) \round($params['rating'], 2);
            update_comment_meta($reviewId, 'rating', $rating);
            update_comment_meta($reviewId, 'reviewx_rating', $rating);
        }
        // 3. Flags (Safe updates using filter_var for string booleans)
        if (isset($params['verified'])) {
            update_comment_meta($reviewId, 'verified', \filter_var($params['verified'], \FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($params['is_recommended'])) {
            update_comment_meta($reviewId, 'is_recommended', \filter_var($params['is_recommended'], \FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }
        if (isset($params['is_anonymous'])) {
            update_comment_meta($reviewId, 'is_anonymous', \filter_var($params['is_anonymous'], \FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }
        // 4. Attachments
        if (isset($params['attachments'])) {
            update_comment_meta($reviewId, 'reviewx_attachments', $params['attachments'] ?? []);
        }
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
        // $uploaded_images = isset($files['attachments']) ? $files['attachments'] : [];
        // $attachments = $this->fileUpload($uploaded_images);
        $criterias = Helper::arrayGet($payloadData, 'criterias');
        $rating = Helper::arrayGet($payloadData, 'rating', null);
        if ($criterias) {
            $wp_post_id = $payloadData['wp_post_id'] ?? null;
            $isAllowedMultiCriteria = $wp_post_id ? (new \Rvx\Services\SettingService())->getReviewSettings(get_post_type($wp_post_id))['reviews']['multicriteria']['enable'] ?? \false : \true;
            if ($isAllowedMultiCriteria) {
                $rating = $this->calculateAverageRating($criterias);
            }
        }
        $payloadData['rating'] = $rating ? (float) \round($rating, 2) : (float) 0.0;
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
    public function getWidgetAllReviewsForSite($request, $site_id)
    {
        return (new ReviewsApi())->getWidgetAllReviewsForSiteApi($request, $site_id);
    }
    public function getWidgetReviewsListShortcode($request)
    {
        return (new ReviewsApi())->getWidgetReviewsListShortcodeApi($request);
    }
    public function settingMeta($request)
    {
        try {
            $settings = $request->get_params()['meta'];
            (new \Rvx\Services\SettingService())->updateSettingsData($settings);
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
    public function reviewBulkUpdate($data)
    {
        return TransactionManager::run(function () use($data) {
            $this->reviewBulkStatusUpdateForWp($data);
            return \true;
        }, function () use($data) {
            return $this->reviewApi->reviewBulkUpdate($data);
        });
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
        return TransactionManager::run(function () use($data) {
            $this->bulkTrashInWp($data);
            return \true;
        }, function () use($data) {
            return $this->reviewApi->reviewBulkTrash($data);
        });
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
        return TransactionManager::run(function () use($data) {
            $this->emptyTrashInWp($data);
            return \true;
        }, function () use($data) {
            return $this->reviewApi->reviewEmptyTrash();
        });
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
        return TransactionManager::run(function () use($request) {
            return $this->dataMerge($request);
        }, function ($data) {
            return $this->reviewApi->saveWidgetReviewsForProductApi($data);
        });
    }
    public function dataMerge($request)
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
        $post_type = get_post_type($productId);
        $review_setting = (new \Rvx\Services\SettingService())->getReviewSettings($post_type);
        $criteria_enabled = $review_setting['reviews']['multicriteria']['enable'];
        if ($criterias && $criteria_enabled === \true) {
            $data['criterias'] = \array_map('intval', $criterias);
            // Rating Modified
            $total_rating = \array_sum($data['criterias']);
            $rating_count = \count($data['criterias']);
            // Count of valid values
            $data['rating'] = $rating_count > 0 ? (float) \round($total_rating / $rating_count, 2) : (float) 0.0;
        } else {
            $data['rating'] = $request['rating'] ? (float) \round($request['rating'], 2) : (float) 0.0;
        }
        return $data;
    }
    public function requestReviewEmailAttachment($request)
    {
        // Get parameters and file payload
        $wpUniqueId = $request->get_params();
        $payload = $request->get_file_params();
        // Initialize the response array
        $response = [];
        // Maximum file size in bytes (e.g., 5MB)
        $maxFileSize = 5 * 1024 * 1024;
        // 5 MB
        // Allowed mime types for images
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
        // Loop through the reviews array
        foreach ($wpUniqueId['reviews'] as $index => $reviewData) {
            if (isset($reviewData['wp_unique_id'])) {
                $wp_unique_id = $this->getLastSegment($reviewData['wp_unique_id']);
                // Prepare the files array for this review
                $files = [];
                if (isset($payload['reviews']['tmp_name'][$index]['files'])) {
                    foreach ($payload['reviews']['tmp_name'][$index]['files'] as $fileIndex => $tmpFile) {
                        // Get the corresponding file details
                        $file_info = ['name' => $payload['reviews']['name'][$index]['files'][$fileIndex]['file'], 'tmp_name' => $tmpFile['file'], 'type' => $payload['reviews']['type'][$index]['files'][$fileIndex]['file'], 'error' => $payload['reviews']['error'][$index]['files'][$fileIndex]['file'], 'size' => $payload['reviews']['size'][$index]['files'][$fileIndex]['file']];
                        // Validate file size
                        if ($file_info['size'] > $maxFileSize) {
                            continue;
                        }
                        // Validate file type (mime type)
                        if (!\in_array($file_info['type'], $allowedMimeTypes)) {
                            continue;
                        }
                        if ($file_info['error'] === \UPLOAD_ERR_OK) {
                            // Upload the file to WordPress
                            $upload = wp_handle_upload($file_info, ['test_form' => \false]);
                            if (!isset($upload['error']) && isset($upload['url'])) {
                                // Add the file URL to the files array
                                $files[] = ['file' => $upload['url']];
                            }
                        }
                    }
                }
                $image_urls = \array_map(function ($file) {
                    return $file['file'];
                }, $files);
                update_comment_meta($wp_unique_id, 'reviewx_attachments', $image_urls);
                // Add the data for this review
                $response[] = ['wp_unique_id' => Client::getUid() . '-' . $wp_unique_id, 'files' => $files];
            }
        }
        return $response;
    }
    public function reviewMoveToTrash($data)
    {
        return TransactionManager::run(function () use($data) {
            $this->trashInWp($data);
            return \true;
        }, function () use($data) {
            return $this->reviewApi->reviewMoveToTrash($data);
        });
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
            $data = ['wp_id' => (int) $comment->comment_ID, 'product_wp_unique_id' => Client::getUid() . '-' . $comment->comment_post_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'reviewer_email' => $comment->comment_author_email, 'reviewer_name' => $comment->comment_author, 'rating' => (float) \round(get_comment_meta($comment->comment_ID, 'rating', \true), 2), 'feedback' => $comment->comment_content, 'created_at' => $comment->comment_date, 'title' => get_comment_meta($comment->comment_ID, 'rvx_comment_title', \true), 'order_item_wp_unique_id' => get_comment_meta($comment->comment_ID, 'rvx_comment_order_item', \true), 'criterias' => get_comment_meta($comment->comment_ID, 'rvx_criterias', \true)];
            $reviewData['reviews'][] = $data;
        }
        return $reviewData;
    }
    public function prepareWpCommentDataForEmail($data) : array
    {
        //Send email review only product
        $settingsData = (new \Rvx\Services\SettingService())->getReviewSettings('product');
        $auto_approve_reviews = $settingsData['reviews']['auto_approve_reviews'];
        $review_type = 'review';
        if (get_post_type($request['wp_post_id']) !== 'product') {
            $review_type = 'comment';
        }
        $dataStore = [];
        foreach ($data['reviews'] as $review) {
            $dataWp = ['comment_post_ID' => absint($review['product_wp_id']), 'comment_content' => sanitize_text_field($review['feedback'] ?? ''), 'comment_author' => sanitize_text_field($review['reviewer_name']), 'comment_author_email' => sanitize_text_field($review['reviewer_email']), 'comment_type' => $review_type, 'comment_approved' => $auto_approve_reviews === \true ? 1 : 0, 'comment_agent' => $_SERVER['HTTP_USER_AGENT'], 'comment_author_IP' => $_SERVER['REMOTE_ADDR'], 'comment_date_gmt' => current_time('mysql', 1), 'user_id' => absint($review['user_id']) ?? 0, 'comment_date' => current_time('mysql', \true)];
            $dataStore[] = $dataWp;
        }
        return $dataStore;
    }
    public function storeReviewMetaForEmail($data, array $wpCommentData) : array
    {
        try {
            $id = [];
            $isAllowedMultiCriteria = (new \Rvx\Services\SettingService())->getReviewSettings('product')['reviews']['multicriteria']['enable'] ?? \false;
            foreach ($wpCommentData as $index => $comment) {
                $criterias = $data['reviews'][$index]['criterias'] ?? null;
                if (!empty($criterias) && $criterias !== null && $isAllowedMultiCriteria === \true) {
                    // Calculate the average rating
                    $wcAverageRating = $this->calculateAverageRating($criterias);
                    $modified_criteria = \json_encode($criterias);
                } else {
                    $wcAverageRating = (float) sanitize_text_field(\round($data['reviews'][$index]['rating'], 2)) ?? 0.0;
                    $modified_criteria = null;
                }
                $commentId = wp_insert_comment($comment);
                add_comment_meta($commentId, 'rvx_comment_title', sanitize_text_field($data['reviews'][$index]['title'] ?? null));
                update_comment_meta($commentId, 'rvx_criterias', $modified_criteria);
                add_comment_meta($commentId, 'rating', $wcAverageRating);
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
            return (float) 0.0;
            // Fallback value
        }
        // Normalize all values to float
        $values = \array_map(function ($value) {
            return \is_numeric($value) ? (int) $value : 0;
            // Convert numeric values to float, default to 0.00
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
        return $count > 0 ? (float) \round($total / $count, 2) : (float) 0.0;
        // Calculate the average and round up
    }
    public function thanksMessage($request)
    {
        return ['message' => "Thank you for sharing your review"];
    }
    public function setAllReviewsMetaTransient($site_id, $post_type, $latest_reviews)
    {
        $post_type = $post_type != null ? $post_type : 'all';
        set_transient("rvx_{$site_id}_{$post_type}_reviews", wp_slash($latest_reviews), 3600);
        // Expires in 1 hour
    }
    public function postMetaReviewInsert($id, $latest_reviews)
    {
        set_transient("rvx_{$id}_latest_reviews", wp_slash($latest_reviews), 3600);
        // Expires in 1 hour
    }
    public function allReviewApproveCount() : int
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) \n             FROM {$wpdb->comments} \n             WHERE comment_approved = '1' \n             AND comment_parent = 0 \n             AND comment_type IN ('review','comment')");
        return (int) $wpdb->get_var($query);
    }
    public function allReviewPendingCount() : int
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) \n        FROM {$wpdb->comments} \n        WHERE comment_approved = '0' \n        AND comment_parent = 0\n        AND comment_type IN ('review','comment')");
        return (int) $wpdb->get_var($query);
    }
    public function saasStatusReviewCount()
    {
        $data = \get_transient('rvx_reviews_data_list');
        if (\is_array($data)) {
            return $data['count'];
        }
        return [];
    }
    public function makeSaaSCallDecision()
    {
        $approveReviewCount = $this->allReviewApproveCount();
        $pendingReviewCount = $this->allReviewPendingCount();
        $saasApproveReviewCount = \array_key_exists('published', $this->saasStatusReviewCount()) ? $this->saasStatusReviewCount()['published'] : 0;
        $saasPendingReviewCount = \array_key_exists('pending', $this->saasStatusReviewCount()) ? $this->saasStatusReviewCount()['pending'] : 0;
        if ($approveReviewCount != $saasApproveReviewCount) {
            return \true;
        }
        if ($saasPendingReviewCount != $pendingReviewCount) {
            return \true;
        }
        return \false;
    }
}
