<?php

namespace Rvx\CPT;

use WP_Post;
class CptRichSchemaHandler
{
    /**
     * Process and add rich schema data to custom post type markup.
     *
     * @param array   $markup    Existing schema markup.
     * @param WP_Post $post      WordPress post object (could be any post type).
     * @return array Updated schema markup.
     */
    public function schemaHandler($markup, $post) : array
    {
        // Fetch the average rating for this post (if it exists)
        $averageRating = (float) get_post_meta($post->ID, 'rvx_avg_rating', \true);
        // Fetch all approved comments (reviews) for the post (non-product post types)
        $reviews = get_comments(['post_id' => $post->ID, 'status' => 'approve', 'type' => 'comment']);
        // Initialize review count
        $reviewCount = 0;
        $markup = [];
        if (!empty($reviews)) {
            //$markup['review'] = [];
            foreach ($reviews as $review) {
                // Skip comment replies by checking if it's a reply to another comment
                if ($review->comment_parent > 0) {
                    continue;
                    // Skip replies
                }
                // Increment review count for actual reviews
                $reviewCount++;
                // Get post type dynamically
                $postType = get_post_type($post);
                // Add review data to the markup array
                $markup['review'][] = ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $review->comment_author], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (float) get_comment_meta($review->comment_ID, 'rating', \true)], 'datePublished' => get_comment_date('c', $review), 'description' => $review->comment_content, 'itemReviewed' => [
                    '@type' => \ucfirst($postType),
                    // Dynamically set the post type name
                    'name' => $post->post_title,
                    // The post being reviewed
                    'url' => get_permalink($post),
                ]];
            }
        }
        // Add aggregate rating if reviews exist and have an average rating
        if ($reviewCount > 0 && $averageRating > 0) {
            $markup['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $averageRating, 'reviewCount' => $reviewCount, 'itemReviewed' => [
                '@type' => \ucfirst($postType),
                // Dynamically set the post type name
                'name' => $post->post_title,
                // The post being reviewed
                'url' => get_permalink($post),
            ]];
        }
        // Include schema type for non-product posts (adjusted for non-product scenario)
        $markup['@context'] = 'https://schema.org/';
        // $markup['@type'] = ucfirst(get_post_type($post)); // Dynamically set the post type name
        // $markup['@id'] = get_permalink($post);
        // $markup['name'] = $post->post_title;
        // $markup['url'] = get_permalink($post);
        // $markup['description'] = wp_trim_words($post->post_content, 20); // Adjust length if needed
        // $markup['image'] = get_the_post_thumbnail_url($post->ID, 'full') ?: ''; // Optional: If the post has an image
        return $markup;
    }
    /**
     * Adds custom schema to the head of the page for non-product post types.
     */
    public static function addCustomRichSchema()
    {
        // List of post types to target
        $enabled_post_types = (new \Rvx\CPT\CptHelper())->enabledCPT();
        unset($enabled_post_types['product']);
        // Unset Product
        $post_type = get_post_type();
        if (!empty($enabled_post_types[$post_type]) && $enabled_post_types[$post_type] !== $post_type) {
            return;
        }
        // Only run on single post.
        if (is_singular()) {
            global $post;
            // Instantiate the class
            $handler = new self();
            // Process the schema for the current post
            $markup = $handler->schemaHandler([], $post);
            // Output the schema markup inside a script tag in the page head
            echo '<script type="application/ld+json">' . \json_encode($markup, \JSON_UNESCAPED_UNICODE) . '</script>';
        }
    }
}
