<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
class CommentsReviewsRowActionRemover
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Remove specific row actions from comments and reviews based on the post type and comment type.
     *
     * @param array      $actions The array of actions for the comment row.
     * @param WP_Comment $comment The comment object.
     * @return array The modified actions array.
     */
    public function removeCommentsReviewsRowActions($actions, $comment)
    {
        // List of post types to target (include product)
        $enabled_post_types = $this->cptHelper->enabledCPT();
        // Get the post type of the comment
        $post_type = get_post_type($comment->comment_post_ID);
        // Get the comment type (WooCommerce product reviews have a type 'review')
        $comment_type = $comment->comment_type;
        // List of actions to remove
        $actions_to_remove = ['reply', 'quickedit', 'edit'];
        // If the post type matches and the comment type is 'comment' or 'review', remove the specified actions
        if (\in_array($post_type, $enabled_post_types, \true) && \in_array($comment_type, ['comment', 'review'], \true)) {
            foreach ($actions_to_remove as $action) {
                if (isset($actions[$action])) {
                    unset($actions[$action]);
                }
            }
        }
        return $actions;
    }
}
