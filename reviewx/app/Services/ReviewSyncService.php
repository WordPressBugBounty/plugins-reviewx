<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Handlers\MigrationRollback\MigrationPrompt;
use ReviewX\Handlers\MigrationRollback\ReviewXChecker;
use ReviewX\Utilities\Helper;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Facades\DB;
use ReviewX\Services\ReviewService;
class ReviewSyncService extends \ReviewX\Services\Service
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
    protected $reviewMetaOrder;
    protected $reviewMetaOrderItem;
    protected $orderCustomerRelation = [];
    protected $criteria;
    protected $procesedReviews;
    protected $commentReplyRelation;
    protected $reviewMetaIdsSeen;
    protected $reviewMetaTitleV2;
    protected $reviewMetaCommentTitleV2;
    protected $reviewMetaTitleV1;
    protected $reviewMetaAttachmentsV2;
    protected $reviewMetaAttachmentsV1;
    protected $reviewMetaRecommendedV2;
    protected $reviewMetaRecommendedV1;
    protected $reviewMetaAnonymousV2;
    protected $reviewMetaAnonymousV1;
    protected $reviewMetaCriteriaV2;
    protected $reviewMetaCriteriaV1;
    protected $reviewMetaCriteriaWC;
    protected ReviewService $reviewService;
    protected MigrationPrompt $migrationData;
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->migrationData = new MigrationPrompt();
        if (ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
            $this->criteria = \get_option('_rx_option_review_criteria') ?? [];
        } elseif (ReviewXChecker::isReviewXSaasExists()) {
            $this->criteria = (new \ReviewX\Services\SettingService())->getReviewSettings('product')['reviews']['multicriteria']["criterias"] ?? [];
        } else {
            $this->criteria = [];
        }
    }
    public function getCriteria()
    {
        return $this->criteria;
    }
    public function processReviewForSync(&$buffer, $post_type) : int
    {
        $this->syncReviewMata();
        return $this->syncReview($buffer, $post_type);
    }
    public function syncReview(&$buffer, $post_type) : int
    {
        $this->procesedReviews = [];
        $this->reviewids = [];
        $this->reviewRelationId = [];
        $reviewCount = 0;
        $this->loadCommentReplyRelation(postType: $post_type);
        //WC Reviews / CPT Reviews
        $review_type = $post_type === 'product' ? ['review'] : ['comment'];
        DB::table('comments')->join('posts', 'posts.ID', '=', 'comments.comment_post_ID')->where('posts.post_type', $post_type)->where('comment_parent', '=', 0)->whereIn('comment_type', $review_type)->chunk(100, function ($comments) use(&$buffer, &$reviewCount) {
            foreach ($comments as $comment) {
                $this->procesedReviews = $this->processReview($comment);
                if (Helper::appendToJsonl($buffer, $this->procesedReviews)) {
                    $reviewCount++;
                }
            }
        });
        // Review sync count is returned to the caller.
        return $reviewCount;
    }
    public function syncReviewsByIds(&$buffer, array $commentIds) : int
    {
        $commentIds = \array_values(\array_unique(\array_filter(\array_map('intval', $commentIds))));
        if (empty($commentIds)) {
            return 0;
        }
        $this->procesedReviews = [];
        $this->reviewids = [];
        $this->reviewRelationId = [];
        $reviewCount = 0;
        $this->syncReviewMata($commentIds);
        $this->loadCommentReplyRelation(commentIds: $commentIds);
        DB::table('comments')->join('posts', 'posts.ID', '=', 'comments.comment_post_ID')->whereIn('comments.comment_ID', $commentIds)->where('comment_parent', '=', 0)->orderBy('comments.comment_ID')->chunk(100, function ($comments) use(&$buffer, &$reviewCount) {
            foreach ($comments as $comment) {
                $this->procesedReviews = $this->processReview($comment);
                if (Helper::appendToJsonl($buffer, $this->procesedReviews)) {
                    $reviewCount++;
                }
            }
        });
        return $reviewCount;
    }
    public function processReview($comment) : array
    {
        $reply = null;
        $repliedAt = null;
        if (!empty($this->commentReplyRelation[$comment->comment_ID]) && \is_array($this->commentReplyRelation[$comment->comment_ID])) {
            $replyData = $this->commentReplyRelation[$comment->comment_ID][0] ?? null;
            $reply = \is_array($replyData) ? $replyData['reply'] ?? null : null;
            $repliedAt = \is_array($replyData) ? $replyData['replied_at'] ?? null : null;
        }
        $trashed_at = null;
        if ($comment->comment_approved === 'trash') {
            $status = $this->getTrashedCommentStatus((int) $comment->comment_ID);
            $metaTrashTime = $this->reviewTrashTime[$comment->comment_ID] ?? null;
            $trashed_at = $metaTrashTime ? Helper::validateReturnDate($metaTrashTime) : null;
        } else {
            $status = $this->getCommentStatus($comment);
        }
        return ['rid' => 'rid://Review/' . (int) $comment->comment_ID, 'product_id' => (int) $comment->comment_post_ID, 'wp_id' => (int) $comment->comment_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'rating' => isset($this->reviewMetaRating[$comment->comment_ID]) ? (float) \round($this->reviewMetaRating[$comment->comment_ID], 2) : (float) 0.0, 'reviewer_email' => $comment->comment_author_email ?? null, 'reviewer_name' => $comment->comment_author ?? null, 'title' => isset($this->reviewMetaTitle[$comment->comment_ID]) ? $this->reviewMetaTitle[$comment->comment_ID] : null, 'feedback' => $comment->comment_content ?? null, 'verified' => !empty($this->reviewMetaVerified[$comment->comment_ID]), 'attachments' => $this->reviewMetaAttachmentsAll[$comment->comment_ID] ?? [], 'is_recommended' => !empty($this->reviewMetaRecommended[$comment->comment_ID]), 'is_anonymous' => !empty($this->reviewMetaAnonymous[$comment->comment_ID]), 'status' => $status, 'reply' => $reply, 'replied_at' => $repliedAt, 'trashed_at' => $trashed_at, 'created_at' => Helper::validateReturnDate($comment->comment_date_gmt) ?? null, 'customer_id' => $this->getReviewCustomerId($comment), 'order_wp_unique_id' => isset($this->reviewMetaOrder[$comment->comment_ID]) ? Client::getUid() . '-' . $this->reviewMetaOrder[$comment->comment_ID] : null, 'order_item_wp_unique_id' => $this->reviewMetaOrderItem[$comment->comment_ID] ?? null, 'ip' => $comment->comment_author_IP ?? null, 'criterias' => $this->reviewMultiCriteriasRating[$comment->comment_ID] ?? null];
    }
    private function getReviewCustomerId($comment) : ?string
    {
        $orderId = $this->reviewMetaOrder[$comment->comment_ID] ?? null;
        // If we have an order ID, try to get the customer from the order relation
        if ($orderId && isset($this->orderCustomerRelation[(int) $orderId])) {
            $customerId = $this->orderCustomerRelation[(int) $orderId];
            return Client::getUid() . '-' . $customerId;
        }
        // Fallback to comment user_id, but only if it's not likely to be an admin
        if ($comment->user_id) {
            // For now, we'll trust user_id if no order is linked.
            return Client::getUid() . '-' . $comment->user_id;
        }
        return null;
    }
    public function syncReviewMata(array $commentIds = []) : void
    {
        $this->resetReviewMetaState();
        $query = DB::table('commentmeta')->whereIn('meta_key', ['rvx_review_version', 'rvx_title', 'rvx_comment_title', 'reviewx_title', 'verified', 'rating', 'rvx_criterias', 'reviewx_rating', 'rvx_attachments', 'reviewx_attachments', 'reviewx_video_url', 'is_recommended', 'reviewx_recommended', 'is_anonymous', 'reviewx_anonymous', '_wp_trash_meta_status', '_wp_trash_meta_time', 'reviewx_order', 'rvx_comment_order_item']);
        if (!empty($commentIds)) {
            $query->whereIn('comment_id', $commentIds);
        }
        $query->chunk(100, function ($allCommentMeta) {
            $orderIds = [];
            foreach ($allCommentMeta as $commentMeta) {
                $commentId = $commentMeta->comment_id;
                $this->reviewMetaIdsSeen[$commentId] = \true;
                if ($commentMeta->meta_key === 'reviewx_order') {
                    $this->reviewMetaOrder[$commentId] = $commentMeta->meta_value;
                    $orderIds[] = (int) $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'rvx_comment_order_item') {
                    $this->reviewMetaOrderItem[$commentId] = $commentMeta->meta_value;
                }
                // Process each meta_key
                if ($commentMeta->meta_key === 'rvx_title') {
                    $this->reviewMetaTitleV2[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'rvx_comment_title') {
                    $this->reviewMetaCommentTitleV2[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'reviewx_title') {
                    $this->reviewMetaTitleV1[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'verified') {
                    $this->reviewMetaVerified[$commentId] = $this->parseBooleanMetaValue($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'rating') {
                    $this->reviewMetaRating[$commentId] = $commentMeta->meta_value;
                    if (!ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
                        $criteria = $this->criteriaMappingWC($commentId, $commentMeta->meta_value);
                        if ($this->criteriaHasUsableData($criteria)) {
                            $this->reviewMetaCriteriaWC[$commentId] = $criteria;
                        }
                    }
                }
                if ($commentMeta->meta_key === 'rvx_criterias') {
                    $criteria = $this->criteriaMappingV2($commentId, $commentMeta->meta_value);
                    if ($this->criteriaHasUsableData($criteria)) {
                        $this->reviewMetaCriteriaV2[$commentId] = $criteria;
                    }
                }
                if ($commentMeta->meta_key === 'rvx_attachments') {
                    $this->reviewMetaAttachmentsV2[$commentId] = $this->mergeAttachmentSets($this->reviewMetaAttachmentsV2[$commentId] ?? [], $this->attachmentsV2($commentId, $commentMeta->meta_value));
                }
                if ($commentMeta->meta_key === 'reviewx_rating') {
                    $criteria = $this->criteriaMappingV1($commentId, $commentMeta->meta_value);
                    if ($this->criteriaHasUsableData($criteria)) {
                        $this->reviewMetaCriteriaV1[$commentId] = $criteria;
                    }
                }
                if (\in_array($commentMeta->meta_key, ['reviewx_attachments', 'reviewx_video_url'], \true)) {
                    $metaData = ['reviewx_attachments' => $commentMeta->meta_key === 'reviewx_attachments' ? $commentMeta->meta_value : null, 'reviewx_video_url' => $commentMeta->meta_key === 'reviewx_video_url' ? $commentMeta->meta_value : null];
                    $this->reviewMetaAttachmentsV1[$commentId] = $this->mergeAttachmentSets($this->reviewMetaAttachmentsV1[$commentId] ?? [], $this->attachmentsV1($commentId, $metaData));
                }
                if ($commentMeta->meta_key === 'is_recommended') {
                    $this->reviewMetaRecommendedV2[$commentId] = $this->parseBooleanMetaValue($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'reviewx_recommended') {
                    $this->reviewMetaRecommendedV1[$commentId] = $this->parseBooleanMetaValue($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'is_anonymous') {
                    $this->reviewMetaAnonymousV2[$commentId] = $this->parseBooleanMetaValue($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'reviewx_anonymous') {
                    $this->reviewMetaAnonymousV1[$commentId] = $this->parseBooleanMetaValue($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === '_wp_trash_meta_status') {
                    $this->reviewTrashStatus[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === '_wp_trash_meta_time') {
                    $this->reviewTrashTime[$commentId] = $commentMeta->meta_value;
                }
            }
            // Pre-fetch customer IDs for found orders to avoid N+1 queries
            if (!empty($orderIds)) {
                $orderStats = DB::table('wc_order_stats')->whereIn('order_id', \array_unique($orderIds))->select(['order_id', 'customer_id'])->get();
                foreach ($orderStats as $stat) {
                    $this->orderCustomerRelation[(int) $stat->order_id] = (int) $stat->customer_id;
                }
            }
        });
        $this->resolveCollectedReviewMeta();
    }
    private function resetReviewMetaState() : void
    {
        $this->reviewMetaTitle = [];
        $this->reviewMetaRating = [];
        $this->reviewMetaVerified = [];
        $this->reviewMetaAttachmentsAll = [];
        $this->reviewMetaRecommended = [];
        $this->reviewMetaAnonymous = [];
        $this->reviewMultiCriteriasRating = [];
        $this->reviewTrashStatus = [];
        $this->reviewTrashTime = [];
        $this->reviewMetaOrder = [];
        $this->reviewMetaOrderItem = [];
        $this->orderCustomerRelation = [];
        $this->reviewMetaIdsSeen = [];
        $this->reviewMetaTitleV2 = [];
        $this->reviewMetaCommentTitleV2 = [];
        $this->reviewMetaTitleV1 = [];
        $this->reviewMetaAttachmentsV2 = [];
        $this->reviewMetaAttachmentsV1 = [];
        $this->reviewMetaRecommendedV2 = [];
        $this->reviewMetaRecommendedV1 = [];
        $this->reviewMetaAnonymousV2 = [];
        $this->reviewMetaAnonymousV1 = [];
        $this->reviewMetaCriteriaV2 = [];
        $this->reviewMetaCriteriaV1 = [];
        $this->reviewMetaCriteriaWC = [];
    }
    private function resolveCollectedReviewMeta() : void
    {
        foreach (\array_keys($this->reviewMetaIdsSeen ?? []) as $commentId) {
            $resolvedTitle = $this->firstUsableText([$this->reviewMetaTitleV2[$commentId] ?? null, $this->reviewMetaCommentTitleV2[$commentId] ?? null, $this->reviewMetaTitleV1[$commentId] ?? null]);
            if ($resolvedTitle !== null) {
                $this->reviewMetaTitle[$commentId] = $resolvedTitle;
            }
            $v2Attachments = $this->reviewMetaAttachmentsV2[$commentId] ?? [];
            $v1Attachments = $this->reviewMetaAttachmentsV1[$commentId] ?? [];
            if (!empty($v2Attachments)) {
                $this->reviewMetaAttachmentsAll[$commentId] = $v2Attachments;
            } elseif (!empty($v1Attachments)) {
                $this->reviewMetaAttachmentsAll[$commentId] = $v1Attachments;
            }
            if (\array_key_exists($commentId, $this->reviewMetaRecommendedV2)) {
                $this->reviewMetaRecommended[$commentId] = $this->reviewMetaRecommendedV2[$commentId];
            } elseif (\array_key_exists($commentId, $this->reviewMetaRecommendedV1)) {
                $this->reviewMetaRecommended[$commentId] = $this->reviewMetaRecommendedV1[$commentId];
            }
            if (\array_key_exists($commentId, $this->reviewMetaAnonymousV2)) {
                $this->reviewMetaAnonymous[$commentId] = $this->reviewMetaAnonymousV2[$commentId];
            } elseif (\array_key_exists($commentId, $this->reviewMetaAnonymousV1)) {
                $this->reviewMetaAnonymous[$commentId] = $this->reviewMetaAnonymousV1[$commentId];
            }
            if (isset($this->reviewMetaCriteriaV2[$commentId])) {
                $this->reviewMultiCriteriasRating[$commentId] = $this->reviewMetaCriteriaV2[$commentId];
            } elseif (isset($this->reviewMetaCriteriaV1[$commentId])) {
                $this->reviewMultiCriteriasRating[$commentId] = $this->reviewMetaCriteriaV1[$commentId];
            } elseif (isset($this->reviewMetaCriteriaWC[$commentId])) {
                $this->reviewMultiCriteriasRating[$commentId] = $this->reviewMetaCriteriaWC[$commentId];
            }
            if ((!isset($this->reviewMetaRating[$commentId]) || !\is_numeric($this->reviewMetaRating[$commentId]) || (float) $this->reviewMetaRating[$commentId] <= 0) && isset($this->reviewMultiCriteriasRating[$commentId]) && $this->criteriaHasUsableData($this->reviewMultiCriteriasRating[$commentId])) {
                $this->reviewMetaRating[$commentId] = $this->reviewService->calculateAverageRating($this->reviewMultiCriteriasRating[$commentId]);
            }
        }
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
    private function getTrashedCommentStatus(int $comment_id) : string
    {
        $status = $this->reviewTrashStatus[$comment_id] ?? null;
        if ($status === 0 || $status === '0' || $status === 'hold' || $status === 'pending' || $status === 'unapproved') {
            return 'pending';
        }
        if ($status === 'spam') {
            return 'spam';
        }
        return 'published';
    }
    private function criteriaMappingWC($commentId, $metaValue)
    {
        unset($commentId);
        $metaValue = \maybe_unserialize($metaValue) ?? 0;
        if (!\is_array($metaValue) && !\is_numeric($metaValue)) {
            return null;
        }
        if (\is_numeric($metaValue)) {
            $metaValue = [$metaValue];
        }
        $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        $newArray = [];
        $i = 0;
        foreach ($keys as $key) {
            $newArray[$key] = isset($metaValue[$i]) ? (int) $metaValue[$i] : 0;
            $i++;
        }
        return $this->criteriaHasUsableData($newArray) ? $newArray : null;
    }
    private function criteriaMappingV1($commentId, $metaValue)
    {
        unset($commentId);
        $metaValue = \maybe_unserialize($metaValue);
        $multCritria_data = $this->getCriteria();
        if (empty($multCritria_data)) {
            return null;
        }
        $criteriaKeys = [];
        $index = 0;
        foreach ($multCritria_data as $key => $name) {
            $criteriaKeys[$key] = \chr(97 + $index);
            $index++;
        }
        $newCriteria = [];
        if (\is_array($metaValue) && !empty($metaValue)) {
            foreach ($metaValue as $key => $value) {
                if (isset($criteriaKeys[$key])) {
                    $newCriteria[$criteriaKeys[$key]] = (int) $value;
                }
            }
        }
        $allKeys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        foreach ($allKeys as $key) {
            if (!isset($newCriteria[$key])) {
                $newCriteria[$key] = 0;
            }
        }
        return $this->criteriaHasUsableData($newCriteria) ? $newCriteria : null;
    }
    private function criteriaMappingV2($commentId, $metaValue)
    {
        unset($commentId);
        $metaValue = \maybe_unserialize($metaValue);
        if (!\is_array($metaValue)) {
            return null;
        }
        $newCriteria = $metaValue;
        $allKeys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        foreach ($allKeys as $key) {
            if (!isset($newCriteria[$key])) {
                $newCriteria[$key] = 0;
            } else {
                $newCriteria[$key] = (int) $newCriteria[$key];
            }
        }
        return $this->criteriaHasUsableData($newCriteria) ? $newCriteria : null;
    }
    private function attachmentsV1($commentId, array $metaData) : array
    {
        unset($commentId);
        $links = [];
        foreach ($metaData as $metaKey => $metaValue) {
            if ($metaValue === null) {
                continue;
                // Skip if no value provided for this meta key
            }
            $data = \is_string($metaValue) ? \maybe_unserialize($metaValue) : $metaValue;
            if ($metaKey === 'reviewx_attachments') {
                if (\is_array($data) && isset($data['images']) && \is_array($data['images'])) {
                    foreach ($data['images'] as $image_id) {
                        $image_url = \wp_get_attachment_url($image_id);
                        $normalizedUrl = $this->normalizeAttachmentUrl((string) $image_url);
                        if ($normalizedUrl !== null) {
                            $links[] = $normalizedUrl;
                        }
                    }
                } elseif (\is_array($data)) {
                    foreach ($data as $attachmentValue) {
                        if (\is_numeric($attachmentValue)) {
                            $image_url = \wp_get_attachment_url((int) $attachmentValue);
                            $normalizedUrl = $this->normalizeAttachmentUrl((string) $image_url);
                            if ($normalizedUrl !== null) {
                                $links[] = $normalizedUrl;
                            }
                            continue;
                        }
                        $links = \array_merge($links, $this->extractAttachmentUrls($attachmentValue));
                    }
                } elseif (\is_string($data)) {
                    $links = \array_merge($links, $this->extractAttachmentUrls($data));
                }
            }
            if ($metaKey === 'reviewx_video_url') {
                // Process video attachments
                $videoLinks = [];
                if (\is_array($data)) {
                    foreach ($data as $video_url) {
                        $normalizedUrl = $this->normalizeAttachmentUrl((string) $video_url);
                        if ($normalizedUrl !== null) {
                            $videoLinks[] = $normalizedUrl;
                        }
                    }
                } elseif (\is_string($data)) {
                    $normalizedUrl = $this->normalizeAttachmentUrl($data);
                    if ($normalizedUrl !== null) {
                        $videoLinks[] = $normalizedUrl;
                    }
                }
                // Merge video links into links
                $links = \array_merge($links, $videoLinks);
            }
        }
        $links = $this->cleanAttachmentUrls($links);
        return $links;
    }
    private function attachmentsV2($commentId, $metaValue) : array
    {
        unset($commentId);
        $normalizedMetaValue = \is_string($metaValue) ? \trim(\html_entity_decode($metaValue, \ENT_QUOTES | \ENT_HTML5, 'UTF-8')) : $metaValue;
        $data = \is_string($normalizedMetaValue) ? \maybe_unserialize($normalizedMetaValue) : $normalizedMetaValue;
        if ($data === \false && \is_string($normalizedMetaValue) && $normalizedMetaValue !== 'b:0;') {
            return $this->extractAttachmentUrlsFromString($normalizedMetaValue);
        }
        $links = $this->extractAttachmentUrls($data);
        if (empty($links) && \is_string($normalizedMetaValue)) {
            $links = $this->extractAttachmentUrlsFromString($normalizedMetaValue);
        }
        return $this->cleanAttachmentUrls($links);
    }
    private function mergeAttachmentSets(array $existingLinks, array $newLinks) : array
    {
        return $this->cleanAttachmentUrls(\array_merge($existingLinks, $newLinks));
    }
    private function extractAttachmentUrls($value) : array
    {
        if (\is_array($value)) {
            $links = [];
            foreach ($value as $item) {
                $links = \array_merge($links, $this->extractAttachmentUrls($item));
            }
            return $links;
        }
        if (\is_object($value)) {
            return $this->extractAttachmentUrls((array) $value);
        }
        if (\is_numeric($value)) {
            $attachmentUrl = \wp_get_attachment_url((int) $value);
            $normalizedUrl = \is_string($attachmentUrl) ? $this->normalizeAttachmentUrl($attachmentUrl) : null;
            return $normalizedUrl !== null ? [$normalizedUrl] : [];
        }
        if (!\is_string($value)) {
            return [];
        }
        $trimmedValue = \trim($value);
        if ($trimmedValue !== '' && \ctype_digit($trimmedValue)) {
            $attachmentUrl = \wp_get_attachment_url((int) $trimmedValue);
            $normalizedUrl = \is_string($attachmentUrl) ? $this->normalizeAttachmentUrl($attachmentUrl) : null;
            return $normalizedUrl !== null ? [$normalizedUrl] : [];
        }
        $normalizedUrl = $this->normalizeAttachmentUrl($value);
        if ($normalizedUrl !== null) {
            return [$normalizedUrl];
        }
        $decodedJson = \json_decode($value, \true);
        if (\is_array($decodedJson)) {
            return $this->extractAttachmentUrls($decodedJson);
        }
        return $this->extractAttachmentUrlsFromString($value);
    }
    private function extractAttachmentUrlsFromString(string $value) : array
    {
        $value = \trim(\html_entity_decode($value, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        if ($value === '') {
            return [];
        }
        \preg_match_all('/(?:https?:)?\\/\\/[^\\s"\'<>|]+/i', $value, $matches);
        if (!empty($matches[0])) {
            return $this->cleanAttachmentUrls($matches[0]);
        }
        $normalizedValue = \str_replace(["\r\n", "\r", "\n", ';', '|'], ',', $value);
        return $this->cleanAttachmentUrls(\str_getcsv($normalizedValue, ',', '"', '\\'));
    }
    private function cleanAttachmentUrls(array $links) : array
    {
        $cleanedLinks = [];
        foreach ($links as $link) {
            $normalizedUrl = \is_string($link) ? $this->normalizeAttachmentUrl($link) : null;
            if ($normalizedUrl !== null) {
                $cleanedLinks[] = $normalizedUrl;
            }
        }
        return \array_values(\array_unique($cleanedLinks));
    }
    private function normalizeAttachmentUrl(string $url) : ?string
    {
        $url = \trim($url, " \t\n\r\x00\v\"'");
        $url = \rtrim($url, ',;');
        if ($url === '') {
            return null;
        }
        if (\strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        $url = \str_replace(' ', '%20', $url);
        return \filter_var($url, \FILTER_VALIDATE_URL) ? $url : null;
    }
    private function parseBooleanMetaValue($metaValue) : bool
    {
        return !\in_array($metaValue, ['', '0', 'false', 0, \false], \true);
    }
    private function firstUsableText(array $values) : ?string
    {
        foreach ($values as $value) {
            if (!\is_string($value)) {
                continue;
            }
            $value = \trim($value);
            if ($value !== '') {
                return $value;
            }
        }
        return null;
    }
    private function criteriaHasUsableData($criteria) : bool
    {
        if (!\is_array($criteria) || $criteria === []) {
            return \false;
        }
        foreach ($criteria as $value) {
            if (\is_numeric($value) && (int) $value > 0) {
                return \true;
            }
        }
        return \false;
    }
    private function loadCommentReplyRelation(?array $commentIds = null, ?string $postType = null) : void
    {
        $this->commentReplyRelation = [];
        $query = DB::table('comments')->join('posts', 'posts.ID', '=', 'comments.comment_post_ID')->where('comment_parent', '!=', 0)->orderBy('comments.comment_date_gmt')->orderBy('comments.comment_ID');
        if (!empty($commentIds)) {
            $query->whereIn('comment_parent', \array_values(\array_unique(\array_map('intval', $commentIds))));
        } elseif (!empty($postType)) {
            $query->where('posts.post_type', $postType);
        }
        $query->chunk(100, function ($comments) {
            foreach ($comments as $comment) {
                $this->commentReplyRelation[$comment->comment_parent][] = ['reply' => $comment->comment_content, 'replied_at' => Helper::validateReturnDate($comment->comment_date_gmt ?: $comment->comment_date)];
            }
        });
    }
}
