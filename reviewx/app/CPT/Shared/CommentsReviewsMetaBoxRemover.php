<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
class CommentsReviewsMetaBoxRemover
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Removes the comments meta box from all custom post types and WooCommerce products.
     */
    public function removeCommentsReviewsMetaBox()
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        foreach ($enabled_post_types as $post_type) {
            // Remove the "Allow Comments" meta box
            remove_meta_box('commentstatusdiv', $post_type, 'normal');
            remove_meta_box('commentstatusdiv', $post_type, 'side');
            // Remove the "Comments" meta box displaying existing comments
            remove_meta_box('commentsdiv', $post_type, 'normal');
            remove_meta_box('commentsdiv', $post_type, 'side');
        }
    }
}
