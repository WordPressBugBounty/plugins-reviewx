<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
class PostsRatingColumn
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function addColumn()
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        // Loop through each post type and hook into the actions/filters dynamically
        foreach ($enabled_post_types as $post_type) {
            // Hook into the columns filter for each post type
            add_filter("manage_edit-{$post_type}_columns", function ($columns) use($post_type) {
                return $this->addRatingColumn($columns, $post_type);
            });
            add_action("manage_{$post_type}_posts_custom_column", [$this, 'populateRatingColumn'], 10, 2);
            // if($post_type !== 'product'){
            //     add_filter("manage_edit-{$post_type}_sortable_columns", [$this, 'makeRatingColumnSortable']);
            //     add_action('pre_get_posts', [$this, 'sortPostsByRating']);
            // }
        }
    }
    /**
     * Add a new column to the post type list for the review rating.
     *
     * @param array $columns The existing post columns.
     * @return array Modified columns array.
     */
    public function addRatingColumn($columns, $post_type)
    {
        // For 'product' post type, add rating column after the title column
        if ($post_type === 'product') {
            // Find the position of the 'author' column
            $price_position = \array_search('price', \array_keys($columns));
            // Insert the 'rating' column after the 'price' column
            if ($price_position !== \false) {
                // Add the 'rating' column after the 'author' column
                $columns = \array_slice($columns, 0, $price_position + 1, \true) + ['rating' => __('ReviewX Rating', 'reviewx')] + \array_slice($columns, $price_position + 1, null, \true);
            }
        } else {
            // For other post types, find the position of the 'author' column
            $author_position = \array_search('author', \array_keys($columns));
            if ($author_position !== \false) {
                $columns = \array_slice($columns, 0, $author_position + 1, \true) + ['rating' => __('ReviewX Rating', 'reviewx')] + \array_slice($columns, $author_position + 1, null, \true);
            }
        }
        return $columns;
    }
    /**
     * Populate the rating column with the post's rating.
     *
     * @param string $column The column name.
     * @param int    $post_id The ID of the post.
     */
    public function populateRatingColumn($column, $post_id)
    {
        if ($column === 'rating') {
            $post_type = get_post_type($post_id);
            $meta_key = 'product' === $post_type ? '_wc_average_rating' : 'rvx_avg_rating';
            $rating = get_post_meta($post_id, $meta_key, \true);
            echo $this->getStarsHtml($rating ? $rating : 0);
            // Default to 0 if no rating
        }
    }
    /**
     * Generate the HTML for the stars based on the rating value.
     *
     * @param int $rating The rating value (1-5).
     * @return string The HTML output for the stars.
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
     * Make the rating column sortable.
     *
     * @param array $columns The existing sortable columns.
     * @return array Modified sortable columns array.
     */
    public function makeRatingColumnSortable($columns)
    {
        $columns['rating'] = 'rating';
        return $columns;
    }
    /**
     * Sort posts by the rating column.
     *
     * @param WP_Query $query The WP_Query object.
     */
    public function sortPostsByRating($query)
    {
        if (is_admin() && isset($_GET['orderby']) && 'rating' === $_GET['orderby']) {
            $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
            $meta_key = get_post_type() === 'product' ? '_wc_average_rating' : 'rvx_avg_rating';
            $query->set('meta_key', $meta_key);
            $query->set('orderby', 'meta_value_num');
            $query->set('order', $order);
        }
    }
}
