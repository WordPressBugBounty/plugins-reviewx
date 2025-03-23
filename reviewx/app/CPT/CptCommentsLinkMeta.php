<?php

namespace Rvx\CPT;

use Rvx\CPT\CptReviewsCount;
use Rvx\CPT\CptHelper;
class CptCommentsLinkMeta
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Modify the comment count output with custom review logic
     */
    public function replace_total_comments_count($count, $post_id)
    {
        // List of post types to target
        $enabled_post_types = $this->cptHelper->usedCPT('enabled');
        unset($enabled_post_types['product']);
        // Unset Product
        $post_type = get_post_type($post_id);
        // Exclude post type
        if (!empty($enabled_post_types[$post_type]) && $enabled_post_types[$post_type] !== $post_type) {
            return $count;
            // No changes
        }
        $reviewCount = (new CptReviewsCount())->newCount($post_id);
        if (!empty($reviewCount[0]) && $reviewCount[0] > 0) {
            // Return the filtered review count without replies
            return $reviewCount[0];
        } else {
            return $count;
        }
    }
}
