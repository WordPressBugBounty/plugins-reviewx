<?php

namespace Rvx\Services;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class ReviewSyncService extends \Rvx\Services\Service
{
    protected $reviewMetaTitle;
    protected $reviewRelationId;
    protected $reviewids;
    protected $reviewMetaRating;
    protected $reviewMetaVerified;
    protected $reviewMetaAttachments;
    protected $reviewMetaRecommended;
    protected $reviewMetaAnonymous;
    protected $reviewMulticritriya;
    protected $reviewTrashStatus;
    protected $reviewTrashTime;
    protected $criteria;
    protected $procesedReviews;
    protected $datasyncProductProcess;
    protected $commentReplyRelation = [];
    public function __construct(\Rvx\Services\ProductSyncService $datasyncProductProcess)
    {
        $this->datasyncProductProcess = $datasyncProductProcess;
        $this->criteria = get_option('_rx_option_review_criteria');
    }
    public function getCriteria()
    {
        return $this->criteria;
    }
    public function processReviewForSync($file) : int
    {
        $this->syncReviewMata();
        return $this->syncReview($file);
    }
    public function syncReview($file) : int
    {
        $this->procesedReviews = [];
        $this->reviewids = [];
        $this->reviewRelationId = [];
        $reviewCount = 0;
        //Reply
        DB::table('comments')->where('comment_parent', '!=', 0)->chunk(100, function ($comments) use(&$file) {
            foreach ($comments as $comment) {
                $this->commentReplyRelation[$comment->comment_parent][] = [$comment->comment_ID => $comment->comment_content];
            }
        });
        //Review & comment
        DB::table('comments')->where('comment_parent', '=', 0)->whereIn('comment_type', ['review', 'comment'])->chunk(100, function ($comments) use(&$commentReplyRelation, &$file, &$reviewCount) {
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
            $trashed_at = $metaTrashTime ? \wp_date('Y-m-d H:i:s', $metaTrashTime) : null;
        } else {
            $status = $this->getCommentStatus($comment);
        }
        return ['rid' => 'rid://Review/' . (int) $comment->comment_ID, 'product_id' => (int) $comment->comment_post_ID, 'wp_id' => (int) $comment->comment_ID, 'wp_post_id' => (int) $comment->comment_post_ID, 'rating' => (int) $this->reviewMetaRating[$comment->comment_ID] ?? null, 'reviewer_email' => $comment->comment_author_email ?? null, 'reviewer_name' => $comment->comment_author ?? null, 'title' => $this->reviewMetaTitle[$comment->comment_ID] ?? null, 'feedback' => $comment->comment_content ?? null, 'verified' => !empty($this->reviewMetaVerified[$comment->comment_ID]), 'attachments' => $this->reviewMetaAttachments[$comment->comment_ID] ?? [], 'is_recommended' => !empty($this->reviewMetaRecommended[$comment->comment_ID]), 'is_anonymous' => !empty($this->reviewMetaAnonymous[$comment->comment_ID]), 'status' => $status, 'reply' => $reply, 'trashed_at' => $trashed_at, 'created_at' => \wp_date('Y-m-d H:i:s', \strtotime($comment->comment_date)) ?? null, 'customer_id' => $comment->user_id ?? null, 'ip' => $comment->comment_author_IP ?? null, 'criterias' => $this->reviewMulticritriya[$comment->comment_ID] ?? []];
    }
    public function syncReviewMata() : void
    {
        DB::table('commentmeta')->whereIn('meta_key', ['reviewx_title', 'rating', 'verified', 'reviewx_attachments', 'is_recommended', 'is_anonymous', '_wp_trash_meta_status', '_wp_trash_meta_time', 'rvx_review_version'])->chunk(100, function ($allCommentMeta) {
            foreach ($allCommentMeta as $commentMeta) {
                $commentId = $commentMeta->comment_id;
                // Process each meta_key
                if ($commentMeta->meta_key === 'reviewx_title') {
                    $this->reviewMetaTitle[$commentId] = $commentMeta->meta_value;
                }
                if ($commentMeta->meta_key === 'rating') {
                    $this->reviewMetaRating[$commentId] = $commentMeta->meta_value;
                    $this->reviewMulticritriya[$commentId] = $this->criteriaMapping($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'verified') {
                    $this->reviewMetaVerified[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMeta->meta_key === 'reviewx_attachments') {
                    $this->reviewMetaAttachments[$commentId] = $this->attachments($commentMeta->meta_value);
                }
                if ($commentMeta->meta_key === 'is_recommended') {
                    $this->reviewMetaRecommended[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMeta->meta_key === 'is_anonymous') {
                    $this->reviewMetaAnonymous[$commentId] = !\in_array($commentMeta->meta_value, ['', '0', 'false', 0, \false], \true);
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
    public function getCommentStatus($comment) : ?string
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
    public function criteriaMapping($reviewRatingData)
    {
        $process_data = maybe_unserialize($reviewRatingData);
        $multCritria_data = $this->getCriteria();
        if (empty($multCritria_data)) {
            return;
        }
        $mapped_array = [];
        foreach ($multCritria_data as $key => $value) {
            if (isset($process_data[$key])) {
                $mapped_array[$value] = (int) $process_data[$key];
            }
        }
        $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        $newArray = \array_fill_keys($keys, 0);
        $i = 0;
        foreach ($mapped_array as $value) {
            if (isset($keys[$i])) {
                $newArray[$keys[$i]] = $value;
            }
            $i++;
        }
        if ($newArray == []) {
            return $newArray = null;
        }
        return $newArray;
    }
    private function attachments($attachments)
    {
        //        $imageUrls = $this->datasyncProductProcess->getProductAttachementRalation();
        $data = \is_string($attachments) ? maybe_unserialize($attachments) : [];
        $links = [];
        if (\is_array($data) && isset($data['images'])) {
            foreach ($data['images'] as $image_id) {
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    $links[] = $image_url;
                }
            }
            return $links;
        }
        if (\is_array($data) && !isset($data['images'])) {
            return $data;
        }
        return [];
    }
}
