<?php

namespace Rvx\CPT;

class CommentsRatingColumn
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new \Rvx\CPT\CptHelper();
    }
    /**
     * Add a new column to the comments page for the review rating.
     *
     * @param array $columns The existing comment columns.
     * @return array Modified columns array.
     */
    public function addRatingColumn($columns)
    {
        // Find the position of the 'author' column
        $author_position = \array_search('author', \array_keys($columns));
        // Insert the 'rating' column after the 'author' column
        if ($author_position !== \false) {
            // Add the 'rating' column after the 'author' column
            $columns = \array_slice($columns, 0, $author_position + 1, \true) + ['rating' => __('ReviewX Rating', 'reviewx')] + \array_slice($columns, $author_position + 1, null, \true);
        }
        return $columns;
    }
    /**
     * Populate the new column with the rating value.
     *
     * @param string    $column  The column name.
     * @param int       $comment_id The ID of the comment.
     */
    public function populateRatingColumn($column, $comment_id)
    {
        if ($column === 'rating') {
            // Get the comment object
            $comment = get_comment($comment_id);
            // Check if the comment is a parent comment
            if ($comment->comment_parent != 0) {
                return;
                // Skip if it's a reply (i.e., not a parent comment)
            }
            // Get the comment type (WooCommerce product reviews have a type 'review')
            $comment_type = $comment->comment_type;
            // Get the post type of the comment
            $post_type = get_post_type($comment->comment_post_ID);
            // Define the target post types
            $enabled_post_types = $this->cptHelper->enabledCPT();
            unset($enabled_post_types['product']);
            // Unset Product
            // Check if the comment's post type is in the target post types and comment type is 'review' and 'comment'
            if (\in_array($post_type, $enabled_post_types, \true) && \in_array($comment_type, ['comment', 'review'], \true)) {
                // Get the rating meta data for the comment (reviews have 'rating' meta key)
                $rating = get_comment_meta($comment_id, 'rating', \true);
                // If rating exists, display stars, otherwise show empty stars (0)
                if ($rating) {
                    echo $this->getStarsHtml($rating);
                } else {
                    echo $this->getStarsHtml(0);
                    // Empty stars for no rating
                }
            }
        }
    }
    /**
     * Generate the star HTML for the given rating.
     *
     * @param int $rating The rating value (1 to 5).
     * @return string The HTML for the stars.
     */
    public function getStarsHtml($rating)
    {
        $rating = \max(0, \min(5, $rating));
        // Ensure the rating is between 0 and 5
        $star_size = '22px';
        // Customize size if needed
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                // Full star
                $star_symbol = '<span class="dashicons dashicons-star-filled"></span>';
            } elseif ($rating >= $i - 0.5) {
                // Half star
                $star_symbol = '<span class="dashicons dashicons-star-half"></span>';
            } else {
                // Empty star
                $star_symbol = '<span class="dashicons dashicons-star-empty"></span>';
            }
            $stars .= $star_symbol;
        }
        return "<span class='rvx-stars' title='{$rating}' style='font-size: {$star_size}; color: #f5a623;'>{$stars}</span>";
    }
    /**
     * Sort comments based on the 'rating' column.
     *
     * @param WP_Query $query The WP_Query object.
     */
    public function sortCommentsByRating($query)
    {
        // Check if we are in the admin and the correct screen (comments)
        if (is_admin() && isset($_GET['orderby']) && 'rating' === $_GET['orderby']) {
            // Sort by rating in ascending or descending order based on the current 'order' parameter
            $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
            // Modify the query to order by rating
            $query->set('meta_key', 'rating');
            // Meta key for rating
            $query->set('orderby', 'meta_value_num');
            // Order by numeric value of meta field
            $query->set('order', $order);
            // Set the order (ASC or DESC)
        }
    }
    /**
     * Make the 'rating' column sortable.
     *
     * @param array $columns The existing sortable columns.
     * @return array Modified sortable columns array.
     */
    public function makeRatingColumnSortable($columns)
    {
        $columns['rating'] = 'rating';
        return $columns;
    }
}
