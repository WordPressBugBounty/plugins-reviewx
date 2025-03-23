<?php

namespace Rvx\CPT;

class CptReviewsCount
{
    public function newCount($post_id)
    {
        // Fetch all approved comments (reviews) for the post
        $reviews = get_comments(['post_id' => $post_id, 'status' => 'approve', 'type' => 'comment']);
        // Initialize review count
        $totalCount = 0;
        $reviewCount = 0;
        if (!empty($reviews)) {
            foreach ($reviews as $review) {
                $totalCount++;
                // Skip replies
                if ($review->comment_parent > 0) {
                    continue;
                    // Skip replies
                }
                $reviewCount++;
            }
        }
        // If no reviews exist, fall back to original comments count
        if ($reviewCount === 0) {
            return $totalCount;
            // Return original count if no reviews
        }
        return [$reviewCount, $totalCount];
    }
}
