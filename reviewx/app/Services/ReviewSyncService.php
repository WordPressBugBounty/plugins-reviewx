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
        return $reviewCount;
    }
    public function processReview($comment) : array
    {
        $reply = null;
        if ($this->commentReplyRelation[$comment->comment_ID] && \count($this->commentReplyRelation[$comment->comment_ID]) > 0) {
            $reply = \reset($this->commentReplyRelation[$comment->comment_ID][0]);
        }
        $trashed_at = null;
        if ($comment->comment_approved === 'trash') {
            $status = $this->reviewTrashStatus[$comment->comment_ID] === 0 ? 'pending' : 'published';
            $metaTrashTime = $this->reviewTrashTime[$comment->comment_ID];
            $trashed_at = $metaTrashTime ? \date('Y-m-d H:i:s', $metaTrashTime) : null;
        } else {
            $status = $this->getCommentStatus($comment);
        }
        return ['rid' => 'rid://Review/' . $comment->comment_ID, 'product_id' => $comment->comment_post_ID, 'wp_id' => $comment->comment_ID, 'wp_post_id' => $comment->comment_post_ID, 'rating' => $this->reviewMetaRating[$comment->comment_ID], 'reviewer_email' => $comment->comment_author_email, 'reviewer_name' => $comment->comment_author, 'title' => \trim($this->reviewMetaTitle[$comment->comment_ID], '"') ?? null, 'feedback' => \strip_tags($comment->comment_content, '"') ?? null, 'verified' => $this->reviewMetaVerified[$comment->comment_ID] ?? \false, 'attachments' => $this->reviewMetaAttachments[$comment->comment_ID] ?? [], 'is_recommended' => $this->reviewMetaRecommended[$comment->comment_ID] ?? \false, 'is_anonymous' => $this->reviewMetaAnonymous[$comment->comment_ID] ?? \false, 'status' => $status, 'reply' => $reply, 'trashed_at' => $trashed_at, 'created_at' => $comment->comment_date, 'customer_id' => $comment->user_id, 'ip' => $comment->comment_author_IP, 'criterias' => $this->reviewMulticritriya[$comment->comment_ID]];
    }
    public function syncReviewMata() : void
    {
        DB::table('commentmeta')->chunk(100, function ($allCommentMeta) {
            foreach ($allCommentMeta as $commentMetas) {
                if ($commentMetas->meta_key === 'reviewx_title') {
                    $this->reviewMetaTitle[$commentMetas->comment_id] = $commentMetas->meta_value;
                }
                if ($commentMetas->meta_key === 'rating') {
                    $this->reviewMetaRating[$commentMetas->comment_id] = $commentMetas->meta_value;
                }
                if ($commentMetas->meta_key === 'verified') {
                    $this->reviewMetaVerified[$commentMetas->comment_id] = !\in_array($commentMetas->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMetas->meta_key === 'reviewx_attachments') {
                    $this->reviewMetaAttachments[$commentMetas->comment_id] = $this->attachments($commentMetas->meta_value);
                }
                if ($commentMetas->meta_key === 'is_recommended') {
                    $this->reviewMetaRecommended[$commentMetas->comment_id] = !\in_array($commentMetas->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMetas->meta_key === 'is_anonymous') {
                    $this->reviewMetaAnonymous[$commentMetas->comment_id] = !\in_array($commentMetas->meta_value, ['', '0', 'false', 0, \false], \true);
                }
                if ($commentMetas->meta_key === 'rating') {
                    $this->reviewMulticritriya[$commentMetas->comment_id] = $this->criteriaMapping($commentMetas->meta_value);
                }
                if ($commentMetas->meta_key === '_wp_trash_meta_status') {
                    $this->reviewTrashStatus[$commentMetas->comment_id] = $commentMetas->meta_value;
                }
                if ($commentMetas->meta_key === '_wp_trash_meta_time') {
                    $this->reviewTrashTime[$commentMetas->comment_id] = $commentMetas->meta_value;
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
        $imageUrls = $this->datasyncProductProcess->getProductAttachementRalation();
        $data = \is_string($attachments) ? \unserialize($attachments) : '';
        $links = [];
        if ($data !== \false && isset($data['images'])) {
            foreach ($data['images'] as $image_id) {
                if (isset($imageUrls[$image_id])) {
                    $links[] = $imageUrls[$image_id];
                }
            }
            return $links;
        }
    }
}
