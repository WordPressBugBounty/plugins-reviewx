<?php

namespace ReviewX\Services;

\defined('ABSPATH') || exit;
use Exception;
use ReviewX\Api\ReviewsApi;
use ReviewX\Enum\ReviewStatusEnum;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Utilities\Helper;
use ReviewX\Utilities\TransactionManager;
use ReviewX\Utilities\UploadMimeSupport;
use ReviewX\WPDrill\Response;
class ReviewService extends \ReviewX\Services\Service
{
    protected static int $skipDeletedCommentSync = 0;
    protected ReviewsApi $reviewApi;
    public function __construct()
    {
        $this->reviewApi = new ReviewsApi();
    }
    public static function withDeletedCommentSyncSuspended(callable $callback)
    {
        self::$skipDeletedCommentSync++;
        try {
            return $callback();
        } finally {
            self::$skipDeletedCommentSync = \max(0, self::$skipDeletedCommentSync - 1);
        }
    }
    public static function shouldSkipDeletedCommentSync() : bool
    {
        return self::$skipDeletedCommentSync > 0;
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
            $storedRating = \get_comment_meta($commentId, 'rating', \true);
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
                        throw new Exception(\esc_html__('File size exceeds 100MB limit', 'reviewx'));
                    }
                }
                $attachments = $this->fileUpload($uploaded_images);
            } else {
                $attachments = $data->get_params()['attachments'] ? $data->get_params()['attachments'] : [];
            }
            $criterias = isset($data->get_params()['criterias']) ? $data->get_params()['criterias'] : null;
            $isAllowedMultiCriteria = (new \ReviewX\Services\SettingService())->getReviewSettings(\get_post_type($data->get_params()['wp_post_id']))['reviews']['multicriteria']['enable'] ?? \false;
            // Calculate the average rating
            if ($criterias !== null && $isAllowedMultiCriteria === \true) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
            } else {
                $wcAverageRating = (float) \round($data->get_params()['rating'], 2);
            }
            $wpCommentData['comment_meta'] = ['rvx_title' => \wp_strip_all_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null), 'is_recommended' => \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0, 'verified' => \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified'], 'is_anonymous' => \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0, 'rvx_criterias' => $criterias, 'rating' => $wcAverageRating, 'rvx_attachments' => $attachments, 'rvx_review_version' => 'v2'];
            $commentId = \wp_insert_comment($wpCommentData);
            if (!\is_wp_error($commentId) && $commentId > 0) {
                \ReviewX\CPT\CptAverageRating::update_average_rating($wpCommentData['comment_post_ID']);
                return $commentId;
            }
            return 0;
        } catch (Exception $e) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is not direct output
            throw new Exception(\esc_html__("Review Save field", 'reviewx') . \esc_html($e->getMessage()));
        }
    }
    public function storeReviewMetaFormWidget($data, array $wpCommentData, $file = [])
    {
        try {
            $attachments = !empty($file) ? $file : [];
            $criterias = isset($data->get_params()['criterias']) ? $data->get_params()['criterias'] : null;
            // Calculate the average rating
            $isAllowedMultiCriteria = (new \ReviewX\Services\SettingService())->getReviewSettings(\get_post_type($data->get_params()['wp_post_id']))['reviews']['multicriteria']['enable'] ?? \false;
            if ($criterias !== null && $isAllowedMultiCriteria === \true) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
            } else {
                $wcAverageRating = \round($data->get_params()['rating'], 2);
            }
            $wpCommentData['comment_meta'] = ['rvx_title' => \wp_strip_all_tags(\array_key_exists('title', $data->get_params()) ? $data->get_params()['title'] : null), 'is_recommended' => \array_key_exists('is_recommended', $data->get_params()) && $data->get_params()['is_recommended'] === "true" ? 1 : 0, 'verified' => \array_key_exists('verified', $data->get_params()) && $data->get_params()['verified'], 'is_anonymous' => \array_key_exists('is_anonymous', $data->get_params()) && $data->get_params()['is_anonymous'] === "true" ? 1 : 0, 'rvx_criterias' => $criterias, 'rating' => $wcAverageRating, 'rvx_attachments' => $attachments, 'rvx_review_version' => 'v2'];
            $commentId = \wp_insert_comment($wpCommentData);
            if (!\is_wp_error($commentId) && $commentId > 0) {
                \ReviewX\CPT\CptAverageRating::update_average_rating($wpCommentData['comment_post_ID']);
                return (int) $commentId;
            }
            return 0;
        } catch (Exception $e) {
            // Silence exception log for production
        }
    }
    public function prepareWpCommentData($request) : array
    {
        $data = (array) (new \ReviewX\Services\SettingService())->getReviewSettings(\get_post_type($request['wp_post_id']));
        $review_type = 'review';
        if (\get_post_type($request['wp_post_id']) !== 'product') {
            $review_type = 'comment';
        }
        $default_status = !empty($data['reviews']['auto_approve_reviews']) ? 'approve' : 'hold';
        $status = $this->mapSubmittedStatusToWpCommentStatus(Helper::arrayGet($request->get_params(), 'status'), $default_status);
        return ['comment_post_ID' => \absint($request['wp_post_id']), 'comment_content' => \wp_strip_all_tags($request->get_param('feedback')), 'comment_author' => \sanitize_text_field($request['reviewer_name']), 'comment_author_email' => \sanitize_text_field($request['reviewer_email']), 'comment_type' => $review_type, 'comment_approved' => $status, 'comment_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '', 'comment_author_IP' => isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : '', 'comment_date_gmt' => \current_time('mysql', 1), 'user_id' => \sanitize_text_field($request['user_id']) ?? 0, 'comment_date' => \current_time('mysql', \true)];
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
        $data = ['wp_id' => $commentId, 'product_wp_unique_id' => Client::getUid() . '-' . $payloadData['wp_post_id'] ?? null, 'wp_post_id' => $payloadData['wp_post_id'] ?? null, 'reviewer_email' => \sanitize_text_field($payloadData['reviewer_email'] ?? null), 'reviewer_name' => \sanitize_text_field($payloadData['reviewer_name'] ?? null), 'rating' => (float) \sanitize_text_field(\round($payloadData['rating'], 2) ?? 0.0), 'feedback' => \wp_strip_all_tags($payloadData['feedback'] ?? null), 'is_verified' => Helper::arrayGet($payloadData, 'verified'), 'auto_publish' => Helper::arrayGet($payloadData, 'status'), 'created_at' => \current_time('mysql', \true), 'title' => \wp_strip_all_tags($payloadData['title'] ?? null), 'attachments' => isset($payloadData['attachments']) ? $payloadData['attachments'] : []];
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
            $upload = UploadMimeSupport::withAllowedUploads(function () use($file) {
                return \wp_handle_upload($file, UploadMimeSupport::getWpHandleUploadOverrides());
            });
            if (isset($upload['url'])) {
                $attachment_id = \wp_insert_attachment(['guid' => $upload['url'], 'post_mime_type' => $upload['type'], 'post_title' => \sanitize_file_name($file['name']), 'post_content' => '', 'post_status' => 'publish'], $upload['file']);
                $attachment_data = UploadMimeSupport::generateAttachmentMetadata($attachment_id, $upload['file'], $upload['type'] ?? null);
                if (!empty($attachment_data)) {
                    \wp_update_attachment_metadata($attachment_id, $attachment_data);
                }
                $uploaded_urls[] = \wp_get_attachment_url($attachment_id);
            }
        }
        return $uploaded_urls;
    }
    public function deleteReview($request)
    {
        $wpUniqueId = \sanitize_text_field($request->get_param('wpUniqueId'));
        $review_id = $this->getLastSegment($wpUniqueId);
        if (\get_comment($review_id)) {
            $deletedInWp = self::withDeletedCommentSyncSuspended(function () use($request) {
                return $this->reviewDelete($request->get_params());
            });
            if (!$deletedInWp) {
                return Helper::rest(null)->fails(\esc_html__("Review Delete Fails", "reviewx"));
            }
        }
        $delete_rev = (new ReviewsApi())->deleteReviewData($wpUniqueId);
        if ($delete_rev && $delete_rev->getStatusCode() >= 200 && $delete_rev->getStatusCode() < 300) {
            $this->reviewCacheDelete($review_id);
        }
        return Helper::saasResponse($delete_rev);
    }
    public function reviewDelete($data) : bool
    {
        $parts = \explode('-', $data['wpUniqueId']);
        $last_part = \end($parts);
        $comment = \get_comment((int) $last_part);
        if (!$comment) {
            return \false;
        }
        return $this->deleteCommentTreeInWp((int) $last_part);
    }
    public function restoreReview($request)
    {
        $wpUniqueId = \sanitize_text_field($request->get_param('wpUniqueId'));
        $status = $request->get_param('status');
        return TransactionManager::run(function () use($wpUniqueId, $status) {
            if (!$this->restoreTrashToStatus($wpUniqueId, $status)) {
                throw new Exception(\esc_html__('Review restore failed in WordPress', 'reviewx'));
            }
            return $this->resolveReviewStatusCode($status);
        }, function ($resolvedStatus) use($wpUniqueId) {
            $resp = $this->reviewApi->restoreReview($wpUniqueId, $resolvedStatus);
            if ($resp->getStatusCode() === 200) {
                $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            }
            return $resp;
        });
    }
    public function restoreTrashToPublish($review_unique_id) : bool
    {
        return $this->restoreTrashToStatus($review_unique_id, null);
    }
    public function restoreTrashToStatus($review_unique_id, $status = null) : bool
    {
        $id = $this->getLastSegment($review_unique_id);
        return $this->restoreCommentFromTrash((int) $id, $status);
    }
    public function getReview($request)
    {
        $wpUniqueId = \sanitize_text_field($request->get_param('wpUniqueId'));
        return (new ReviewsApi())->getReview($wpUniqueId);
    }
    public function restoreTrashItem($data)
    {
        return TransactionManager::run(function () use($data) {
            if (!$this->bulkRestoreTrashItem($data)) {
                throw new Exception(\esc_html__('Bulk review restore failed in WordPress', 'reviewx'));
            }
            return $data;
        }, function ($payload) {
            $response = (new ReviewsApi())->restoreTrashItem($payload);
            if ((int) $response->getStatusCode() === 200) {
                $this->bulkReviewCacheDelete($this->resolveRestoreWpIds($payload));
            }
            return $response;
        });
    }
    public function bulkRestoreTrashItem($data) : bool
    {
        foreach ($this->resolveRestoreWpIds($data) as $id) {
            if (!$this->restoreCommentFromTrash((int) $id, $data['status'] ?? null)) {
                return \false;
            }
        }
        return \true;
    }
    public function isVerify($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $status = $request->get_param('status');
        $verifyData = (new ReviewsApi())->verifyReview($status, $wpUniqueId);
        if ($verifyData) {
            $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            return Helper::rest($verifyData()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(\esc_html__('Verify Fail', 'reviewx'));
    }
    public function isvisibility($request)
    {
        return TransactionManager::run(function () use($request) {
            return $this->visibilitySpam($request->get_params());
        }, function () use($request) {
            $wpUniqueId = $request['wpUniqueId'];
            $statusData = ['status' => $request->get_param('status')];
            $resp = $this->reviewApi->visibilityReviewData($statusData, $wpUniqueId);
            if ($resp->getStatusCode() === 200) {
                $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            }
            return $resp;
        });
    }
    public function visibilitySpam($data)
    {
        if (ReviewStatusEnum::SPAM === $data['status']) {
            \wp_spam_comment($data['wp_id']);
        }
        if (ReviewStatusEnum::APPROVED === $data['status']) {
            \wp_set_comment_status($data['wp_id'], 'approve');
        }
        if (ReviewStatusEnum::PENDING === $data['status']) {
            \wp_set_comment_status($data['wp_id'], 'hold');
        }
        if (ReviewStatusEnum::TRASH === $data['status']) {
            \wp_trash_comment($data['wp_id']);
        }
        return \true;
    }
    public function updateReqEmail($request)
    {
        $data = [];
        $wpUniqueId = $request['wpUniqueId'];
        $verifyData = (new ReviewsApi())->sendUpdateReviewRequestEmail($data, $wpUniqueId);
        if ($verifyData) {
            return Helper::rest($verifyData()->from('data')->toArray())->success(\esc_html__("Verify", "reviewx"));
        }
        return Helper::rest(null)->fails(\esc_html__('Fail', 'reviewx'));
    }
    public function reviewReplies($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $replies = ['reply' => $request['reply'], 'wp_id' => $this->getLastSegment($wpUniqueId)];
        $commentReply = (new ReviewsApi())->commentReply($replies, $wpUniqueId);
        if ($commentReply) {
            $this->reviewRepliesForWp($replies);
            $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            return Helper::rvxApi(['success' => null])->success(\esc_html__('Reply submitted sucesfully.', 'reviewx'), 200);
        }
        return Helper::rest(null)->fails(\esc_html__('Replies Fail', 'reviewx'));
    }
    public function reviewCacheDelete($review_id)
    {
        $comment = \get_comment($review_id);
        $post_id = $comment->comment_post_ID ?? null;
        \delete_transient("rvx_{$post_id}_latest_reviews");
        \delete_transient("rvx_{$post_id}_latest_reviews_insight");
    }
    public function bulkReviewCacheDelete($review_ids)
    {
        if (empty($review_ids)) {
            return;
        }
        $post_ids = [];
        foreach ($review_ids as $id) {
            $comment = \get_comment($id);
            if ($comment && $comment->comment_post_ID) {
                $post_ids[] = $comment->comment_post_ID;
            }
        }
        $post_ids = \array_unique($post_ids);
        foreach ($post_ids as $post_id) {
            \delete_transient("rvx_{$post_id}_latest_reviews");
            \delete_transient("rvx_{$post_id}_latest_reviews_insight");
        }
    }
    public function reviewRepliesForWp($replies)
    {
        $parentReviewId = $replies['wp_id'];
        $parent_comment = \get_comment($parentReviewId);
        if (!$parent_comment) {
            return \false;
        }
        $replyData = $this->prepareDataForReply($parent_comment, $replies, $parentReviewId);
        if ($parent_comment->comment_parent == 0) {
            $replayId = \wp_insert_comment($replyData);
            return \true;
        }
    }
    public function prepareDataForReply($parent_comment, $replies, $parentReviewId)
    {
        return ['comment_post_ID' => $parent_comment->comment_post_ID, 'comment_author' => $parent_comment->comment_author, 'comment_author_email' => $parent_comment->comment_author_email, 'comment_author_url' => '', 'comment_content' => $replies['reply'], 'comment_type' => $parent_comment->comment_type, 'comment_parent' => $parentReviewId, 'user_id' => \get_current_user_id(), 'comment_approved' => 1, 'comment_date' => \current_time('mysql'), 'comment_date_gmt' => \current_time('mysql', 1)];
    }
    private function getLastSegment($string)
    {
        $lastHyphenPos = \strrpos($string, '-');
        if ($lastHyphenPos === \false) {
            return $string;
        }
        return \substr($string, $lastHyphenPos + 1);
    }
    private function resolveRestoreCommentStatus($status) : string
    {
        if ($status === 1 || $status === '1' || $status == 'approve' || $status == ReviewStatusEnum::APPROVED) {
            return 'approve';
        }
        if ($status === 0 || $status === '0' || $status == 'hold' || $status == 'unapproved' || $status == 'pending' || $status == ReviewStatusEnum::PENDING || $status == ReviewStatusEnum::UNPUBLISHED) {
            return 'hold';
        }
        if ($status == 'spam' || $status == ReviewStatusEnum::SPAM) {
            return 'spam';
        }
        return 'approve';
    }
    private function restoreCommentFromTrash(int $comment_id, $requested_status = null) : bool
    {
        $comment = \get_comment($comment_id);
        if (!$comment) {
            return \false;
        }
        $status = $requested_status;
        if ($status === null || $status === '') {
            $status = \get_comment_meta($comment_id, '_wp_trash_meta_status', \true);
        }
        $target_status = $this->resolveRestoreCommentStatus($status);
        $current_status = $this->normalizeCommentApprovedStatus($comment->comment_approved ?? '');
        if ($current_status === 'trash') {
            $untrashed = \wp_untrash_comment($comment_id);
            if ($untrashed === \false) {
                $this->clearTrashCommentMeta($comment_id);
            }
        }
        $comment = \get_comment($comment_id);
        if (!$comment) {
            return \false;
        }
        if ($this->normalizeCommentApprovedStatus($comment->comment_approved ?? '') === 'trash') {
            $this->clearTrashCommentMeta($comment_id);
        }
        if (\wp_set_comment_status($comment_id, $target_status) === \false) {
            return \false;
        }
        \clean_comment_cache($comment_id);
        $updated_comment = \get_comment($comment_id);
        return $updated_comment instanceof \WP_Comment && $this->normalizeCommentApprovedStatus($updated_comment->comment_approved ?? '') === $target_status;
    }
    private function resolveReviewStatusCode($status) : ?int
    {
        if ($status === null || $status === '') {
            return null;
        }
        if ($status === ReviewStatusEnum::APPROVED || $status === 'approve' || $status === 'approved' || $status === 'publish' || $status === 'published' || $status === 1 || $status === '1') {
            return ReviewStatusEnum::APPROVED;
        }
        if ($status === ReviewStatusEnum::PENDING || $status === ReviewStatusEnum::UNPUBLISHED || $status === 'hold' || $status === 'pending' || $status === 'unapproved' || $status === 0 || $status === '0') {
            return ReviewStatusEnum::PENDING;
        }
        if ($status === ReviewStatusEnum::SPAM || $status === 'spam') {
            return ReviewStatusEnum::SPAM;
        }
        return null;
    }
    private function clearTrashCommentMeta(int $comment_id) : void
    {
        \delete_comment_meta($comment_id, '_wp_trash_meta_status');
        \delete_comment_meta($comment_id, '_wp_trash_meta_time');
    }
    private function normalizeCommentApprovedStatus($status) : string
    {
        if ($status === 1 || $status === '1' || $status === 'approve' || $status === 'approved') {
            return 'approve';
        }
        if ($status === 0 || $status === '0' || $status === 'hold' || $status === 'unapproved') {
            return 'hold';
        }
        if ($status === 'spam') {
            return 'spam';
        }
        if ($status === 'trash') {
            return 'trash';
        }
        return (string) $status;
    }
    private function mapSubmittedStatusToWpCommentStatus($status, string $fallbackStatus = 'approve') : string
    {
        if ($status === null || $status === '') {
            return $fallbackStatus;
        }
        if (\is_bool($status)) {
            return $status ? 'approve' : 'hold';
        }
        if (\is_numeric($status)) {
            $numeric_status = (int) $status;
            switch ($numeric_status) {
                case ReviewStatusEnum::APPROVED:
                    return 'approve';
                case ReviewStatusEnum::UNPUBLISHED:
                case ReviewStatusEnum::PENDING:
                case 0:
                    return 'hold';
                case ReviewStatusEnum::SPAM:
                    return 'spam';
                case ReviewStatusEnum::TRASH:
                    return 'trash';
                default:
                    return $fallbackStatus;
            }
        }
        if (\is_string($status)) {
            $normalized_status = \strtolower(\trim($status));
            switch ($normalized_status) {
                case 'approve':
                case 'approved':
                case 'publish':
                case 'published':
                case '1':
                case 'true':
                    return 'approve';
                case 'hold':
                case 'pending':
                case 'unapproved':
                case 'unpublished':
                case 'draft':
                case '0':
                case 'false':
                    return 'hold';
                case 'spam':
                    return 'spam';
                case 'trash':
                    return 'trash';
                default:
                    return $fallbackStatus;
            }
        }
        return $fallbackStatus;
    }
    private function resolveRestoreWpIds(array $data) : array
    {
        $wp_ids = $data['wp_id'] ?? [];
        if (!empty($wp_ids) && \is_array($wp_ids)) {
            return \array_map('intval', $wp_ids);
        }
        $review_unique_ids = $data['review_wp_unique_ids'] ?? [];
        $resolved_ids = [];
        foreach ((array) $review_unique_ids as $review_unique_id) {
            $resolved_ids[] = (int) $this->getLastSegment((string) $review_unique_id);
        }
        return \array_filter($resolved_ids);
    }
    public function reviewRepliesUpdate($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $repliesUpdate = ['reply' => $request['reply']];
        $commentReply = (new ReviewsApi())->updateCommentReply($repliesUpdate, $wpUniqueId);
        if ($commentReply) {
            $this->reviewRepliesUpdateForWp($wpUniqueId, $repliesUpdate);
            $this->reviewCacheDelete($this->getLastSegment($wpUniqueId));
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(\esc_html__('Update Fail', 'reviewx'));
    }
    private function reviewRepliesUpdateForWp($wpUniqueId, $repliesUpdate)
    {
        $parentReviewId = $this->getLastSegment($wpUniqueId);
        $comment_data = array('comment_ID' => $parentReviewId, 'comment_content' => $repliesUpdate['reply'], 'comment_date' => \current_time('mysql'), 'comment_date_gmt' => \current_time('mysql', 1));
        \wp_update_comment($comment_data);
    }
    public function reviewRepliesDelete($request)
    {
        $wpUniqueId = $request['wpUniqueId'];
        $commentReply = (new ReviewsApi())->deleteCommentReply($wpUniqueId);
        if ($commentReply) {
            return Helper::rest($commentReply()->from('data')->toArray())->success();
        }
        return Helper::rest(null)->fails(\esc_html__('Delete Fail', 'reviewx'));
    }
    public function aiReview($request)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
        $wpdb->query('START TRANSACTION');
        try {
            $comment_id = \wp_insert_comment($this->aiReviewWp($request));
            $reviApp = $this->aiReviewApp($request, $comment_id);
            $reviewApi = new ReviewsApi();
            $res = $reviewApi->aiReview($reviApp);
            $resReviewData = $res->getApiData();
            \wp_update_comment($comment_id, $resReviewData);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
            $wpdb->query('COMMIT');
            return $res;
        } catch (Exception $e) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
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
                $aggregation_data = \json_encode(\wp_slash(Helper::arrayGet($data, "meta")), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
                \set_transient("rvx_{$productId}_latest_reviews_insight", $aggregation_data, 604800);
                // Expires in 7 days
            }
            return Helper::rest()->success("Success");
        } catch (Exception $e) {
            return Helper::rest(\esc_html($e->getMessage()))->fails(\esc_html__("Fails", "reviewx"));
        }
    }
    public function aiReviewApp($request, $comment_id)
    {
        return ["wp_id" => $comment_id, "product_wp_unique_id" => $request['product_wp_unique_id'], "wp_post_id" => $request['wp_post_id'], "max_reviews" => $request['max_reviews'], "status" => $request['status'], "verified" => $request['verified'], "region" => $request['region'], "religious" => $request['religious'], "gender" => $request['gender']];
    }
    public function aiReviewWp($request)
    {
        $review_type = 'review';
        if (\get_post_type($request['wp_post_id']) !== 'product') {
            $review_type = 'comment';
        }
        return ['comment_post_ID' => \absint($request['product_id']), 'comment_content' => \sanitize_text_field($request['feedback'] ?? ''), 'comment_author' => \sanitize_text_field(\get_userdata(\get_current_user_id())->display_name), 'comment_author_email' => \sanitize_text_field(\get_userdata(\get_current_user_id())->user_email), 'comment_type' => $review_type, 'comment_approved' => \sanitize_text_field($request['status'] ?? ''), 'comment_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '', 'comment_author_IP' => isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : '', 'comment_date_gmt' => \current_time('mysql', 1), 'comment_date' => \current_time('mysql', \true)];
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
        $feedback = \wp_strip_all_tags($request->get_param('feedback'));
        return TransactionManager::run(function () use($request, $reviewId) {
            $existingReview = \get_comment($reviewId);
            if (!$existingReview) {
                return \false;
            }
            $wpCommentData = $this->prepareUpdateWpComment($request, $existingReview);
            \wp_update_comment(['comment_ID' => $reviewId, 'comment_content' => \wp_strip_all_tags($wpCommentData['comment_content']), 'comment_approved' => \sanitize_text_field($wpCommentData['comment_approved']), 'comment_author_email' => \sanitize_text_field($wpCommentData['comment_author_email']), 'comment_author' => \sanitize_text_field($wpCommentData['comment_author'])]);
            $this->reviewCacheDelete($reviewId);
            $this->updateReviewMeta($reviewId, $request);
            // Update average rating for the post
            \ReviewX\CPT\CptAverageRating::update_average_rating($existingReview->comment_post_ID);
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
            \update_comment_meta($reviewId, 'rvx_title', \sanitize_text_field($params['title']));
        }
        // 2. Rating & Criterias
        $criterias = $params['criterias'] ?? null;
        if ($criterias !== null) {
            \update_comment_meta($reviewId, 'rvx_criterias', $criterias);
            $wp_post_id = $params['wp_post_id'] ?? \get_comment($reviewId)->comment_post_ID;
            $isAllowedMultiCriteria = (new \ReviewX\Services\SettingService())->getReviewSettings(\get_post_type($wp_post_id))['reviews']['multicriteria']['enable'] ?? \false;
            if ($isAllowedMultiCriteria) {
                $wcAverageRating = $this->calculateAverageRating($criterias);
                \update_comment_meta($reviewId, 'rating', $wcAverageRating);
                \update_comment_meta($reviewId, 'rvx_rating', $wcAverageRating);
            }
        } elseif (isset($params['rating'])) {
            $rating = (float) \round($params['rating'], 2);
            \update_comment_meta($reviewId, 'rating', $rating);
            \update_comment_meta($reviewId, 'rvx_rating', $rating);
        }
        // 3. Flags (Safe updates using filter_var for string booleans)
        if (isset($params['verified'])) {
            \update_comment_meta($reviewId, 'verified', \filter_var($params['verified'], \FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($params['is_recommended'])) {
            \update_comment_meta($reviewId, 'is_recommended', \filter_var($params['is_recommended'], \FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }
        if (isset($params['is_anonymous'])) {
            \update_comment_meta($reviewId, 'is_anonymous', \filter_var($params['is_anonymous'], \FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }
        // 4. Attachments
        if (isset($params['attachments'])) {
            \update_comment_meta($reviewId, 'rvx_attachments', $params['attachments'] ?? []);
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
        return ['comment_content' => \wp_strip_all_tags($request->get_param('feedback') ?? $existingReview->comment_content), 'comment_approved' => $this->mapSubmittedStatusToWpCommentStatus($request->get_param('status'), $this->normalizeCommentApprovedStatus($existingReview->comment_approved ?? '')), 'comment_author_email' => \sanitize_text_field($request['reviewer_email'] ?? $existingReview->comment_author_email), 'comment_author' => \sanitize_text_field($request['reviewer_name'] ?? $existingReview->comment_author)];
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
            $isAllowedMultiCriteria = $wp_post_id ? (new \ReviewX\Services\SettingService())->getReviewSettings(\get_post_type($wp_post_id))['reviews']['multicriteria']['enable'] ?? \false : \true;
            if ($isAllowedMultiCriteria) {
                $rating = $this->calculateAverageRating($criterias);
            }
        }
        $payloadData['rating'] = $rating ? (float) \round($rating, 2) : (float) 0.0;
        $data = [
            // 'rating' => (int)$payloadData['rating'],
            'feedback' => \wp_strip_all_tags($payloadData['feedback']),
            'title' => \wp_strip_all_tags($payloadData['title']),
            'reviewer_name' => \sanitize_text_field($payloadData['reviewer_name']),
            'reviewer_email' => \sanitize_text_field($payloadData['reviewer_email']),
            'date' => \current_time('mysql', \true),
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
            $settings = $request->get_param('meta');
            if (\is_array($settings)) {
                (new \ReviewX\Services\SettingService())->updateSettingsData($settings);
            }
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
            $resp = $this->reviewApi->reviewBulkUpdate($data);
            if ($resp->getStatusCode() === 200) {
                $this->bulkReviewCacheDelete($data['wp_id'] ?? []);
            }
            return $resp;
        });
    }
    public function reviewBulkStatusUpdateForWp($data)
    {
        if (ReviewStatusEnum::SPAM === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                \wp_spam_comment($id);
                // Also mark replies as spam
                $replies = \get_comments(['parent' => $id]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \wp_spam_comment($reply->comment_ID);
                    }
                }
            }
        }
        if (ReviewStatusEnum::APPROVED === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                \wp_set_comment_status($id, 'approve');
                // Also approve replies
                $replies = \get_comments(['parent' => $id]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \wp_set_comment_status($reply->comment_ID, 'approve');
                    }
                }
            }
        }
        if (ReviewStatusEnum::PENDING === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                \wp_set_comment_status($id, 'hold');
                // Also hold replies
                $replies = \get_comments(['parent' => $id]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \wp_set_comment_status($reply->comment_ID, 'hold');
                    }
                }
            }
        }
        if (ReviewStatusEnum::TRASH === $data['status']) {
            foreach ($data['wp_id'] as $id) {
                \wp_trash_comment($id);
                // Also trash replies
                $replies = \get_comments(['parent' => $id]);
                if (!empty($replies)) {
                    foreach ($replies as $reply) {
                        \wp_trash_comment($reply->comment_ID);
                    }
                }
            }
        }
    }
    public function reviewBulkTrash($data)
    {
        return TransactionManager::run(function () use($data) {
            $this->bulkTrashInWp($data);
            return \true;
        }, function () use($data) {
            $resp = $this->reviewApi->reviewBulkTrash($data);
            if ($resp->getStatusCode() === 200) {
                $this->bulkReviewCacheDelete($data['wp_id'] ?? []);
            }
            return $resp;
        });
    }
    public function bulkTrashInWp($data)
    {
        if (!\is_array($data)) {
            return \false;
        }
        foreach ($data['wp_id'] as $review_id) {
            \wp_trash_comment($review_id, \true);
            // Also trash replies
            $replies = \get_comments(['parent' => $review_id, 'status' => 'all']);
            if (!empty($replies)) {
                foreach ($replies as $reply) {
                    \wp_trash_comment($reply->comment_ID, \true);
                }
            }
        }
    }
    public function reviewBulkSoftDelete($data)
    {
        $cleared_ids = self::withDeletedCommentSyncSuspended(function () use($data) {
            return $this->emptyTrashInWp($data);
        });
        // Always call SaaS API to ensure we return a valid ApiResponse object
        // that the controller's Helper::saasResponse expects.
        return $this->reviewApi->reviewBulkSoftDelete(['wp_id' => $cleared_ids]);
    }
    public function reviewEmptyTrash($data)
    {
        // Check if this is a targeted bulk delete from the trash tab or a global empty
        $is_targeted = !empty($data['wp_ids']) || !empty($data['wp_id']) || !empty($data['review_ids']);
        $cleared_ids = self::withDeletedCommentSyncSuspended(function () use($data) {
            return $this->emptyTrashInWp($data);
        });
        if ($is_targeted) {
            return $this->reviewApi->reviewBulkSoftDelete(['wp_id' => $cleared_ids]);
        }
        return $this->reviewApi->reviewEmptyTrash();
    }
    public function reviewEmptySpam($data)
    {
        $cleared_ids = self::withDeletedCommentSyncSuspended(function () use($data) {
            return $this->emptySpamInWp($data);
        });
        return $this->reviewApi->reviewEmptySpam(['wp_id' => $cleared_ids]);
    }
    public function emptyTrashInWp($data)
    {
        return $this->emptyCommentsInWpByStatus($data, 'trash');
    }
    public function emptySpamInWp($data)
    {
        return $this->emptyCommentsInWpByStatus($data, 'spam');
    }
    private function emptyCommentsInWpByStatus($data, string $status) : array
    {
        $review_ids = $this->extractReviewIdsFromPayload($data);
        if (empty($review_ids)) {
            $review_ids = $this->getManagedReviewCommentIdsByStatus($status);
        }
        if (empty($review_ids)) {
            return [];
        }
        $cleared_ids = [];
        foreach ($review_ids as $review_id) {
            $review_id = (int) $review_id;
            if ($review_id <= 0) {
                continue;
            }
            if ($this->deleteCommentTreeInWp($review_id)) {
                $cleared_ids[] = $review_id;
            }
        }
        return \array_values(\array_unique(\array_map('intval', $cleared_ids)));
    }
    public function deleteCommentTreeInWp(int $comment_id) : bool
    {
        $comment = \get_comment($comment_id);
        if (!$comment) {
            return \false;
        }
        $deletion_order = $this->getCommentDescendantIdsForDeletion($comment_id);
        $deletion_order[] = $comment_id;
        foreach ($deletion_order as $delete_id) {
            if (!\get_comment($delete_id)) {
                continue;
            }
            if (!\wp_delete_comment($delete_id, \true)) {
                return \false;
            }
        }
        return \true;
    }
    private function getCommentDescendantIdsForDeletion(int $comment_id) : array
    {
        global $wpdb;
        $descendant_ids = [];
        $parent_ids = [$comment_id];
        while (!empty($parent_ids)) {
            $placeholders = \implode(', ', \array_fill(0, \count($parent_ids), '%d'));
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Placeholder list is generated from integer IDs and then safely bound through prepare().
            $child_ids = $wpdb->get_col($wpdb->prepare("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_parent IN ({$placeholders}) ORDER BY comment_ID ASC", $parent_ids));
            $child_ids = \array_values(\array_unique(\array_map('intval', (array) $child_ids)));
            if (empty($child_ids)) {
                break;
            }
            $descendant_ids = \array_merge($descendant_ids, $child_ids);
            $parent_ids = $child_ids;
        }
        return \array_reverse($descendant_ids);
    }
    private function extractReviewIdsFromPayload($data) : array
    {
        $review_ids = [];
        if (isset($data['wp_ids']) && \is_array($data['wp_ids'])) {
            $review_ids = $data['wp_ids'];
        } elseif (isset($data['wp_id']) && \is_array($data['wp_id'])) {
            $review_ids = $data['wp_id'];
        } elseif (isset($data['review_ids']) && \is_array($data['review_ids'])) {
            $review_ids = $data['review_ids'];
        }
        return \array_values(\array_unique(\array_filter(\array_map('intval', (array) $review_ids))));
    }
    private function getManagedReviewCommentIdsByStatus(string $status) : array
    {
        $postTypes = (new \ReviewX\CPT\CptHelper())->usedCPTOnSync('used');
        if (!\is_array($postTypes)) {
            $postTypes = [];
        }
        $postTypes = \array_values(\array_unique(\array_filter(\array_map('sanitize_key', $postTypes))));
        if (empty($postTypes)) {
            $postTypes = ['product'];
        } elseif (!\in_array('product', $postTypes, \true)) {
            $postTypes[] = 'product';
        }
        $args = ['status' => $status, 'fields' => 'ids', 'number' => 0, 'orderby' => 'comment_ID', 'order' => 'ASC', 'parent' => 0, 'post_type' => $postTypes, 'type__in' => ['review', 'comment', ''], 'update_comment_meta_cache' => \false, 'update_comment_post_cache' => \false];
        return \array_values(\array_unique(\array_map('intval', (array) \get_comments($args))));
    }
    public function reviewAggregation($data)
    {
        return (new ReviewsApi())->reviewAggregation($data);
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
        $data = \array_merge($request->get_params(), ["wp_id" => $commentId, "site_uid" => $siteUid, "product_wp_unique_id" => $productWpUniqueId, "is_anonymous" => $request['is_anonymous'] == "true" ? \true : \false, "is_verified" => $request['verified'] == "true" ? \true : \false, "is_customer_verified" => $request['is_customer_verified'] == "true" ? \true : \false, "attachments" => $attachments, 'created_at' => \current_time('mysql', \true), 'is_recommended' => $request['is_recommended'] == "true" ? \true : \false]);
        $data['feedback'] = \wp_strip_all_tags($request['feedback']);
        $data['title'] = \wp_strip_all_tags($request['title']);
        $criterias = Helper::arrayGet($data, 'criterias');
        $post_type = \get_post_type($productId);
        $review_setting = (new \ReviewX\Services\SettingService())->getReviewSettings($post_type);
        $criteria_enabled = $review_setting['reviews']['multicriteria']['enable'] ?? \false;
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
                        // Validate file type from mime and filename so webp/svg can still pass
                        // even when the browser leaves the mime blank or generic.
                        if (!UploadMimeSupport::isAllowedAttachmentFile($file_info['name'] ?? '', $file_info['type'] ?? '')) {
                            continue;
                        }
                        if ($file_info['error'] === \UPLOAD_ERR_OK) {
                            // Upload the file to WordPress
                            $upload = UploadMimeSupport::withAllowedUploads(function () use($file_info) {
                                return \wp_handle_upload($file_info, UploadMimeSupport::getWpHandleUploadOverrides());
                            });
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
                \update_comment_meta($wp_unique_id, 'rvx_attachments', $image_urls);
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
            $resp = $this->reviewApi->reviewMoveToTrash($data);
            if ($resp->getStatusCode() === 200) {
                $this->reviewCacheDelete($this->getLastSegment($data['WpUniqueId']));
            }
            return $resp;
        });
    }
    public function trashInWp($data)
    {
        $parts = \explode('-', $data['WpUniqueId']);
        $last_part = \end($parts);
        \wp_trash_comment($last_part);
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
        $resp = (new ReviewsApi())->highlight($data);
        if ($resp->getStatusCode() === 200) {
            $this->reviewCacheDelete($this->getLastSegment($data['wpUniqueId']));
        }
        return $resp;
    }
    public function bulkTenReviews($data)
    {
        return $data;
    }
    public function reviewRequestStoreItem($data)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
        $wpdb->query('START TRANSACTION');
        try {
            $comment_ids = $this->commentInsertableFormEmail($data);
            $saasData = $this->prepareAppReviewDataFormEmail($comment_ids);
            //  return $this->reviewApi->reviewRequestStoreItem($saasData, $uid);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
            $wpdb->query('COMMIT');
            return $saasData;
        } catch (Exception $e) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction control
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
            $comment = \get_comment($commentId);
            if (!$comment) {
                continue;
            }
            $data = ['wp_id' => (int) $comment->comment_ID, 'product_wp_unique_id' => Client::getUid() . '-' . $comment->comment_post_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'reviewer_email' => $comment->comment_author_email, 'reviewer_name' => $comment->comment_author, 'rating' => (float) \round(\get_comment_meta($comment->comment_ID, 'rating', \true), 2), 'feedback' => $comment->comment_content, 'created_at' => $comment->comment_date, 'title' => \get_comment_meta($comment->comment_ID, 'rvx_comment_title', \true), 'order_item_wp_unique_id' => \get_comment_meta($comment->comment_ID, 'rvx_comment_order_item', \true), 'criterias' => \get_comment_meta($comment->comment_ID, 'rvx_criterias', \true)];
            $reviewData['reviews'][] = $data;
        }
        return $reviewData;
    }
    public function prepareWpCommentDataForEmail($data) : array
    {
        //Send email review only product
        $settingsData = (new \ReviewX\Services\SettingService())->getReviewSettings('product');
        $auto_approve_reviews = $settingsData['reviews']['auto_approve_reviews'];
        $review_type = 'review';
        // For email reviews, we'll check the first review to determine the post type, defaulting to 'product'
        $first_post_id = !empty($data['reviews']) ? $data['reviews'][0]['product_wp_id'] ?? 0 : 0;
        if ($first_post_id && \get_post_type($first_post_id) !== 'product') {
            $review_type = 'comment';
        }
        $dataStore = [];
        foreach ($data['reviews'] as $review) {
            $dataWp = ['comment_post_ID' => \absint($review['product_wp_id']), 'comment_content' => \sanitize_text_field($review['feedback'] ?? ''), 'comment_author' => \sanitize_text_field($review['reviewer_name']), 'comment_author_email' => \sanitize_text_field($review['reviewer_email']), 'comment_type' => $review_type, 'comment_approved' => $auto_approve_reviews === \true ? 1 : 0, 'comment_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '', 'comment_author_IP' => isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : '', 'comment_date_gmt' => \current_time('mysql', 1), 'user_id' => \absint($review['user_id']) ?? 0, 'comment_date' => \current_time('mysql', \true)];
            $dataStore[] = $dataWp;
        }
        return $dataStore;
    }
    public function storeReviewMetaForEmail($data, array $wpCommentData) : array
    {
        try {
            $id = [];
            $isAllowedMultiCriteria = (new \ReviewX\Services\SettingService())->getReviewSettings('product')['reviews']['multicriteria']['enable'] ?? \false;
            foreach ($wpCommentData as $index => $comment) {
                $criterias = $data['reviews'][$index]['criterias'] ?? null;
                if (!empty($criterias) && $criterias !== null && $isAllowedMultiCriteria === \true) {
                    // Calculate the average rating
                    $wcAverageRating = $this->calculateAverageRating($criterias);
                    $modified_criteria = \json_encode($criterias);
                } else {
                    $wcAverageRating = (float) \sanitize_text_field(\round($data['reviews'][$index]['rating'], 2)) ?? 0.0;
                    $modified_criteria = null;
                }
                $commentId = \wp_insert_comment($comment);
                \add_comment_meta($commentId, 'rvx_comment_title', \sanitize_text_field($data['reviews'][$index]['title'] ?? null));
                \update_comment_meta($commentId, 'rvx_criterias', $modified_criteria);
                \add_comment_meta($commentId, 'rating', $wcAverageRating);
                \add_comment_meta($commentId, 'rvx_comment_order_item', \sanitize_text_field($data['reviews'][$index]['order_item_wp_unique_id']));
                \add_comment_meta($commentId, 'verified', 1);
                \add_comment_meta($commentId, 'is_recommended', 1);
                \add_comment_meta($commentId, 'rvx_attachments', []);
                \add_comment_meta($commentId, 'rvx_review_version', 'v2');
                $id[] = $commentId;
            }
            return $id;
        } catch (Exception $e) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is not direct output
            throw new Exception(\esc_html__('Something went wrong!', 'reviewx') . \esc_html($e->getMessage()));
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
        \set_transient("rvx_{$site_id}_{$post_type}_reviews", \wp_slash($latest_reviews), 3600);
        // Expires in 1 hour
    }
    public function postMetaReviewInsert($id, $latest_reviews)
    {
        \set_transient("rvx_{$id}_latest_reviews", \wp_slash($latest_reviews), 3600);
        // Expires in 1 hour
    }
    public function allReviewApproveCount() : int
    {
        return (new \ReviewX\Services\CacheServices())->allReviewApproveCount();
    }
    public function allReviewPendingCount() : int
    {
        return (new \ReviewX\Services\CacheServices())->allReviewPendingCount();
    }
    public function saasStatusReviewCount()
    {
        return (new \ReviewX\Services\CacheServices())->saasStatusReviewCount();
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
