<?php

namespace Rvx\Services;

use Rvx\Handlers\MigrationRollback\MigrationPrompt;
use Rvx\Handlers\MigrationRollback\ReviewXChecker;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
use Rvx\Services\ReviewService;
class ReviewSyncService extends \Rvx\Services\Service
{
    protected $reviewMetaTitle;
    protected $reviewRelationId;
    protected $reviewids;
    protected $reviewMetaRating;
    protected $reviewMetaVerified;
    protected $reviewMetaAttachmentsAll;
    protected $reviewMetaRecommended;
    protected $reviewMetaAnonymous;
    protected $reviewMultiCriteriasRating;
    protected $reviewTrashStatus;
    protected $reviewTrashTime;
    protected $criteria;
    protected $procesedReviews;
    protected $commentReplyRelation;
    protected ReviewService $reviewService;
    protected MigrationPrompt $migrationData;
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->migrationData = new MigrationPrompt();
        if (ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
            $this->criteria = get_option('_rx_option_review_criteria') ?? [];
        } elseif (ReviewXChecker::isReviewXSaasExists()) {
            $this->criteria = (new \Rvx\Services\SettingService())->getReviewSettings('product')['reviews']['multicriteria']["criterias"] ?? [];
        } else {
            $this->criteria = [];
        }
    }
    public function getCriteria()
    {
        return $this->criteria;
    }
    public function processReviewForSync($file, $post_type) : int
    {
        $this->syncReviewMata();
        return $this->syncReview($file, $post_type);
    }
    public function syncReview($file, $post_type) : int
    {
        $this->procesedReviews = [];
        $this->reviewids = [];
        $this->reviewRelationId = [];
        $reviewCount = 0;
        //Reply
        DB::table('comments')->join('posts', 'posts.ID', '=', 'comments.comment_post_ID')->where('posts.post_type', $post_type)->where('comment_parent', '!=', 0)->chunk(100, function ($comments) use(&$file) {
            foreach ($comments as $comment) {
                $this->commentReplyRelation[$comment->comment_parent][] = [$comment->comment_ID => $comment->comment_content];
            }
        });
        //WC Reviews / CPT Reviews
        $review_type = $post_type === 'product' ? ['review'] : ['comment'];
        DB::table('comments')->join('posts', 'posts.ID', '=', 'comments.comment_post_ID')->where('posts.post_type', $post_type)->where('comment_parent', '=', 0)->whereIn('comment_type', $review_type)->chunk(100, function ($comments) use(&$commentReplyRelation, &$file, &$reviewCount) {
            foreach ($comments as $comment) {
                $this->procesedReviews = $this->processReview($comment);
                Helper::appendToJsonl($file, $this->procesedReviews);
                $reviewCount++;
            }
        });
        Helper::rvxLog($reviewCount, "Review Done");
        return $reviewCount;
    }
    public function processReview($comment) : array
    {
        $reply = null;
        if (!empty($this->commentReplyRelation[$comment->comment_ID]) && \is_array($this->commentReplyRelation[$comment->comment_ID])) {
            $replyData = $this->commentReplyRelation[$comment->comment_ID][0] ?? null;
            $reply = $replyData ? \reset($replyData) : null;
        }
        $trashed_at = null;
        if ($comment->comment_approved === 'trash') {
            $status = !empty($this->reviewTrashStatus[$comment->comment_ID]) && $this->reviewTrashStatus[$comment->comment_ID] === 0 ? 'pending' : 'published';
            $metaTrashTime = $this->reviewTrashTime[$comment->comment_ID] ?? null;
            $trashed_at = $metaTrashTime ? Helper::validateReturnDate($metaTrashTime) : null;
        } else {
            $status = $this->getCommentStatus($comment);
        }
        return ['rid' => 'rid://Review/' . (int) $comment->comment_ID, 'product_id' => (int) $comment->comment_post_ID, 'wp_id' => (int) $comment->comment_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'rating' => isset($this->reviewMetaRating[$comment->comment_ID]) ? (int) $this->reviewMetaRating[$comment->comment_ID] : 0, 'reviewer_email' => $comment->comment_author_email ?? null, 'reviewer_name' => $comment->comment_author ?? null, 'title' => isset($this->reviewMetaTitle[$comment->comment_ID]) ? $this->reviewMetaTitle[$comment->comment_ID] : null, 'feedback' => $comment->comment_content ?? null, 'verified' => !empty($this->reviewMetaVerified[$comment->comment_ID]), 'attachments' => $this->reviewMetaAttachmentsAll[$comment->comment_ID] ?? [], 'is_recommended' => !empty($this->reviewMetaRecommended[$comment->comment_ID]), 'is_anonymous' => !empty($this->reviewMetaAnonymous[$comment->comment_ID]), 'status' => $status, 'reply' => $reply, 'trashed_at' => $trashed_at, 'created_at' => Helper::validateReturnDate($comment->comment_date_gmt) ?? null, 'customer_id' => $comment->user_id ?? null, 'ip' => $comment->comment_author_IP ?? null, 'criterias' => $this->reviewMultiCriteriasRating[$comment->comment_ID] ?? null];
    }
    public function syncReviewMata() : void
    {
        DB::table('commentmeta')->whereIn('meta_key', ['rvx_review_version', 'reviewx_title', 'verified', 'rating', 'rvx_criterias', 'reviewx_rating', 'reviewx_attachments', 'reviewx_video_url', 'is_recommended', 'reviewx_recommended', 'is_anonymous', '_wp_trash_meta_status', '_wp_trash_meta_time'])->chunk(100, function ($allCommentMeta) {
            foreach ($allCommentMeta as $commentMeta) {
                $commentId = $commentMeta->comment_id;
                // Process each meta_key
                if ($commentMeta->meta_key === 'reviewx_title') {
                    $this->reviewMetaTitle[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'verified') {
                    $this->reviewMetaVerified[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMeta->meta_key === 'rating') {
                    $this->reviewMetaRating[$commentId] = $commentMeta->meta_value;
                    if (!ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
                        $this->reviewMultiCriteriasRating[$commentId] = $this->criteriaMappingWC($commentId, $commentMeta->meta_value);
                    }
                }
                if (ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
                    if ($commentMeta->meta_key === 'reviewx_rating') {
                        $this->reviewMultiCriteriasRating[$commentId] = $this->criteriaMappingV1($commentId, $commentMeta->meta_value);
                    }
                    if (\in_array($commentMeta->meta_key, ['reviewx_attachments', 'reviewx_video_url'], \true)) {
                        $metaData = ['reviewx_attachments' => $commentMeta->meta_key === 'reviewx_attachments' ? $commentMeta->meta_value : null, 'reviewx_video_url' => $commentMeta->meta_key === 'reviewx_video_url' ? $commentMeta->meta_value : null];
                        // Call attachmentsV1 once for the current $commentId
                        $this->reviewMetaAttachmentsAll[$commentId] = $this->attachmentsV1($commentId, $metaData);
                    }
                    if ($commentMeta->meta_key === 'reviewx_recommended') {
                        $this->reviewMetaRecommended[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
                    }
                    if ($commentMeta->meta_key === 'reviewx_anonymous') {
                        $this->reviewMetaAnonymous[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
                    }
                }
                if (ReviewXChecker::isReviewXSaasExists()) {
                    if ($commentMeta->meta_key === 'rvx_criterias') {
                        $this->reviewMultiCriteriasRating[$commentId] = $this->criteriaMappingV2($commentId, $commentMeta->meta_value);
                    }
                    if ($commentMeta->meta_key === 'reviewx_attachments') {
                        $this->reviewMetaAttachmentsAll[$commentId] = $this->attachmentsV2($commentId, $commentMeta->meta_value);
                    }
                    if ($commentMeta->meta_key === 'is_recommended') {
                        $this->reviewMetaRecommended[$commentId] = $commentMeta->meta_value;
                    }
                    if ($commentMeta->meta_key === 'is_anonymous') {
                        $this->reviewMetaAnonymous[$commentId] = $commentMeta->meta_value;
                    }
                }
                if ($commentMeta->meta_key === '_wp_trash_meta_status') {
                    $this->reviewTrashStatus[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === '_wp_trash_meta_time') {
                    $this->reviewTrashTime[$commentId] = $commentMeta->meta_value;
                }
            }
        });
    }
    private function getCommentStatus($comment) : ?string
    {
        switch ($comment->comment_approved) {
            case '1':
                return 'published';
            case '0':
                return 'pending';
            case 'spam':
                return 'spam';
            default:
                return null;
        }
    }
    private function criteriaMappingWC($commentId, $metaValue)
    {
        $metaValue = maybe_unserialize($metaValue) ?? 0;
        // If $metaValue is not an array or scalar numeric, default to 0
        if (!\is_array($metaValue) && !\is_numeric($metaValue)) {
            $metaValue = 0;
        }
        // If it's a scalar, treat as single rating and wrap in array for consistency
        if (\is_numeric($metaValue)) {
            $metaValue = [$metaValue];
        }
        // Predefined keys a to j (10 keys)
        $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        $newArray = [];
        $i = 0;
        foreach ($keys as $key) {
            // Always add predefined keys, with value from $metaValue or 0
            $newArray[$key] = isset($metaValue[$i]) ? (int) $metaValue[$i] : 0;
            $i++;
        }
        if ($newArray == []) {
            return $newArray = null;
        }
        // Update the comment meta with the new format
        if (!empty($newArray)) {
            update_comment_meta($commentId, 'rvx_criterias', $newArray);
        }
        return $newArray;
    }
    private function criteriaMappingV1($commentId, $metaValue)
    {
        $metaValue = maybe_unserialize($metaValue);
        // Example: a:3:{s:8:"ctr_h8S7";s:1:"3";s:8:"ctr_h8S8";s:1:"3";s:8:"ctr_h8S9";s:1:"3";}
        // Retrieve the existing criteria mapping (old criteria names)
        $multCritria_data = $this->getCriteria();
        // Example: a:3:{s:8:"ctr_h8S7";s:7:"Quality";s:8:"ctr_h8S8";s:5:"Price";s:8:"ctr_h8S9";s:9:"Packaging";}
        if (empty($multCritria_data)) {
            return null;
            // If no criteria data is available, return an empty array
        }
        // Flip multCritria_data to map keys like 'ctr_h8S7' => 'a', 'ctr_h8S8' => 'b', etc.
        $criteriaKeys = [];
        $index = 0;
        foreach ($multCritria_data as $key => $name) {
            // Assign a short key (a, b, c, ...) based on order
            $criteriaKeys[$key] = \chr(97 + $index);
            // ASCII 'a' = 97
            $index++;
        }
        // Initialize the new criteria array in the required format
        $newCriteria = [];
        if (!empty($metaValue)) {
            foreach ($metaValue as $key => $value) {
                if (isset($criteriaKeys[$key])) {
                    // Use the new short key and map it to the value as an integer
                    $newCriteria[$criteriaKeys[$key]] = (int) $value;
                    // 'a' => 3, 'b' => 2
                }
            }
        } else {
            foreach ($criteriaKeys as $shortKey) {
                // If metaValue is empty, assign default value of 0
                $newCriteria[$shortKey] = 0;
                // 'a' => 0, 'b' => 0
            }
        }
        // Update the 'rating' comment meta with the new format
        $ratingValue = get_comment_meta($commentId, 'rating', \true);
        $currentRating = (float) \round(\is_numeric($ratingValue) ? (float) $ratingValue : 0, 2);
        $averageRating = $this->reviewService->calculateAverageRating($newCriteria);
        $critriaAllowed = $this->migrationData->rvx_retrieve_old_plugin_options_data()['multicriteria']['enable'] ?? \false;
        if ($currentRating !== $averageRating && !empty($metaValue) && $critriaAllowed === \true) {
            update_comment_meta($commentId, 'rating', $averageRating);
        }
        // Fill in missing keys ('a' to 'j') with default value 0
        $allKeys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        foreach ($allKeys as $key) {
            if (!isset($newCriteria[$key])) {
                $newCriteria[$key] = 0;
                // Default value for missing keys
            }
        }
        // Update the 'rvx_criterias' comment meta with the new format
        if (!empty($newCriteria)) {
            update_comment_meta($commentId, 'rvx_criterias', $newCriteria);
        }
        return $newCriteria;
    }
    private function criteriaMappingV2($commentId, $metaValue)
    {
        // Deserialize the meta value
        $metaValue = maybe_unserialize($metaValue);
        // Ensure $metaValue is always an array
        if (!\is_array($metaValue)) {
            $metaValue = [];
        }
        // Retrieve the criteria mapping
        $multCritria_data = $this->getCriteria();
        if (empty($multCritria_data)) {
            return null;
            // No criteria data available
        }
        // Initialize the new criteria array
        $newCriteria = $metaValue;
        // Start with existing metaValue if valid
        // Update the 'rating' comment meta with the new format
        $ratingValue = get_comment_meta($commentId, 'rating', \true);
        $currentRating = (float) \round(\is_numeric($ratingValue) ? (float) $ratingValue : 0, 2);
        $averageRating = $this->reviewService->calculateAverageRating($newCriteria);
        $critriaAllowed = $this->migrationData->rvx_retrieve_saas_plugin_options_data()['multicriteria']['enable'] ?? \false;
        if ($currentRating !== $averageRating && !empty($metaValue) && $critriaAllowed === \true) {
            update_comment_meta($commentId, 'rating', $averageRating);
        }
        // Fill in missing keys ('a' to 'j') with default value 0
        $allKeys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        foreach ($allKeys as $key) {
            if (!isset($newCriteria[$key])) {
                $newCriteria[$key] = 0;
                // Default value for missing keys
            } else {
                // Ensure all existing values are integers
                $newCriteria[$key] = (int) $newCriteria[$key];
            }
        }
        // Update the comment meta with the new format
        if (!empty($newCriteria)) {
            update_comment_meta($commentId, 'rvx_criterias', $newCriteria);
        }
        return $newCriteria;
        // Ensure all keys from 'a' to 'j' are present
    }
    private function attachmentsV1($commentId, array $metaData) : array
    {
        $links = [];
        foreach ($metaData as $metaKey => $metaValue) {
            if ($metaValue === null) {
                continue;
                // Skip if no value provided for this meta key
            }
            $data = \is_string($metaValue) ? maybe_unserialize($metaValue) : $metaValue;
            if ($metaKey === 'reviewx_attachments' && \is_array($data) && isset($data['images'])) {
                // Process image attachments
                foreach ($data['images'] as $image_id) {
                    $image_url = wp_get_attachment_url($image_id);
                    if (\filter_var($image_url, \FILTER_VALIDATE_URL)) {
                        $links[] = $image_url;
                    }
                }
            }
            if ($metaKey === 'reviewx_video_url') {
                // Process video attachments
                $videoLinks = [];
                if (\is_array($data)) {
                    foreach ($data as $video_url) {
                        if (\filter_var($video_url, \FILTER_VALIDATE_URL)) {
                            $videoLinks[] = $video_url;
                        }
                    }
                } elseif (\is_string($data) && \filter_var($data, \FILTER_VALIDATE_URL)) {
                    $videoLinks[] = $data;
                }
                // Merge video links into links
                $links = \array_merge($links, $videoLinks);
            }
        }
        // Update the comment meta with IDs for images and URLs for videos
        if (!empty($links)) {
            update_comment_meta($commentId, 'reviewx_attachments', $links);
        }
        return $links;
        // Return URLs
    }
    private function attachmentsV2($commentId, $metaValue) : array
    {
        $data = maybe_unserialize($metaValue);
        $links = [];
        if (\is_array($data)) {
            foreach ($data as $data_url) {
                if (\filter_var($data_url, \FILTER_VALIDATE_URL)) {
                    $links[] = $data_url;
                }
            }
        } elseif (\is_string($data) && \filter_var($data, \FILTER_VALIDATE_URL)) {
            $links[] = $data;
        }
        return $links;
    }
}
