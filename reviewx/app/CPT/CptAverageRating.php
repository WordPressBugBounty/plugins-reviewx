<?php

namespace Rvx\CPT;

use WP_Post;
use Rvx\CPT\CptHelper;
class CptAverageRating
{
    /**
     * Initialize or update the rvx_avg_rating meta key for a post.
     * add_action comment_post
     */
    public static function handle_comment_rating($comment_id, $comment_approved, $comment = null)
    {
        if ($comment_approved !== 1) {
            return;
        }
        if ($comment === null) {
            $comment = get_comment($comment_id);
        }
        if (!$comment) {
            return;
        }
        $post_id = $comment->comment_post_ID;
        // Update the average rating for the post.
        self::update_average_rating($post_id);
    }
    /**
     * Handle when a comment's status is updated.
     *
     * @param int $comment_id The comment ID.
     * @param string $status The new status of the comment.
     */
    public static function handle_comment_status_change($comment_id, $status)
    {
        $comment = get_comment($comment_id);
        if (!$comment) {
            return;
            // Exit if the comment doesn't exist.
        }
        $post_id = $comment->comment_post_ID;
        // Update the average rating for the post.
        self::update_average_rating($post_id);
    }
    /**
     * Calculate and update the rvx_avg_rating meta key for a given post.
     *
     * @param int $post_id ID of the post.
     */
    public static function update_average_rating($post_id)
    {
        // Check the post type.
        $post_type = get_post_type($post_id);
        $enabled_post_types = (new CptHelper())->usedCPT('used');
        if (!isset($enabled_post_types[$post_type]) && $post_type !== 'product') {
            return;
        }
        global $wpdb;
        // Fetch all approved comment ratings for the post, only for parent comments.
        $ratings = $wpdb->get_col($wpdb->prepare("SELECT cm.meta_value \n            FROM {$wpdb->commentmeta} AS cm\n            INNER JOIN {$wpdb->comments} AS c\n            ON cm.comment_id = c.comment_ID\n            WHERE cm.meta_key = 'rating'\n            AND c.comment_post_ID = %d\n            AND c.comment_approved = '1'\n            AND c.comment_parent = 0", $post_id));
        $average_rating = get_post_meta($post_id, 'rvx_avg_rating', \true);
        if (empty($average_rating)) {
            $average_rating = 0.0;
        }
        if (!empty($ratings)) {
            // Calculate the count and average rating using only parent comments.
            $count = \count($ratings);
            $average_rating = \round(\array_sum($ratings) / $count, 2);
            // Calculate individual star counts
            $starCounts = \array_count_values(\array_map('intval', $ratings));
            // Store the average rating and count as post meta
            update_post_meta($post_id, 'rvx_avg_rating', (float) $average_rating);
            update_post_meta($post_id, 'rating', (float) $average_rating);
            update_post_meta($post_id, 'rvx_total_reviews', (int) $count);
            // Store individual star counts
            for ($i = 1; $i <= 5; $i++) {
                update_post_meta($post_id, "rvx_star_count_{$i}", (int) ($starCounts[$i] ?? 0));
            }
            if ($post_type === 'product') {
                update_post_meta($post_id, '_wc_average_rating', (float) $average_rating);
                update_post_meta($post_id, '_wc_review_count', (int) $count);
            }
        } else {
            // No ratings found, set the meta keys to 0.
            update_post_meta($post_id, 'rvx_avg_rating', (float) 0.0);
            update_post_meta($post_id, 'rating', (float) 0.0);
            update_post_meta($post_id, 'rvx_total_reviews', 0);
            for ($i = 1; $i <= 5; $i++) {
                update_post_meta($post_id, "rvx_star_count_{$i}", 0);
            }
            if ($post_type === 'product') {
                update_post_meta($post_id, '_wc_average_rating', (float) 0.0);
                update_post_meta($post_id, '_wc_review_count', 0);
            }
        }
    }
    /**
     * Hook into the post save action to add the rvx_avg_rating meta key.
     *
     * @param int $post_id The post ID.
     * @param WP_Post $post The post object.
     */
    public static function rvx_avg_rating_on_save($post_id, $post)
    {
        // Ensure we are not triggering on autosave
        if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // Check the post type.
        $post_type = get_post_type($post_id);
        $enabled_post_types = (new CptHelper())->usedCPT('used');
        if (!isset($enabled_post_types[$post_type]) && $post_type !== 'product') {
            return;
        }
        // Check if the rvx_avg_rating key already exists
        if (!get_post_meta($post_id, 'rvx_avg_rating', \true)) {
            // Add the rvx_avg_rating meta key with an initial value (0.00)
            update_post_meta($post_id, 'rvx_avg_rating', (float) 0.0);
            update_post_meta($post_id, 'rating', (float) 0.0);
        }
    }
}
