<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
/**
 * Filters comments/reviews from the WP Comments admin page for post types
 * that are enabled in ReviewX.
 */
class CommentsReviewsFilter
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Filter comments query to exclude comments for ReviewX-enabled post types.
     * This hides reviews/comments from the WP Comments admin page.
     *
     * @param WP_Comment_Query $query The comment query object.
     */
    public function filterCommentsForReviewxPostTypes($query)
    {
        // Only execute after sync is completed
        if (!Client::getSync()) {
            return;
        }
        // Only filter on the admin comments page
        if (!is_admin()) {
            return;
        }
        // Check if we're on the comments admin page
        global $pagenow;
        if ($pagenow !== 'edit-comments.php') {
            return;
        }
        // Get enabled post types from ReviewX
        $enabled_post_types = $this->cptHelper->enabledCPT();
        if (empty($enabled_post_types)) {
            return;
        }
        // Get all post IDs that belong to enabled post types
        $post_ids_to_exclude = $this->getPostIdsForPostTypes($enabled_post_types);
        if (empty($post_ids_to_exclude)) {
            return;
        }
        // Get current post__not_in value and merge with our exclusions
        $current_post_not_in = $query->query_vars['post__not_in'] ?? [];
        if (!\is_array($current_post_not_in)) {
            $current_post_not_in = [];
        }
        $query->query_vars['post__not_in'] = \array_merge($current_post_not_in, $post_ids_to_exclude);
    }
    /**
     * Get all post IDs for the given post types.
     *
     * @param array $post_types Array of post type slugs.
     * @return array Array of post IDs.
     */
    protected function getPostIdsForPostTypes($post_types)
    {
        global $wpdb;
        if (empty($post_types)) {
            return [];
        }
        // Build placeholders for IN clause
        $placeholders = \implode(', ', \array_fill(0, \count($post_types), '%s'));
        // Prepare query to get post IDs for the enabled post types
        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ({$placeholders})", \array_values($post_types));
        $post_ids = $wpdb->get_col($query);
        return \array_map('intval', $post_ids);
    }
}
