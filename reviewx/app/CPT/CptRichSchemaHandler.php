<?php

namespace Rvx\CPT;

use Rvx\Services\SettingService;
use WP_Post;
/**
 * Handles rich schema generation for non-product post types (CPTs, pages, posts, etc.).
 */
class CptRichSchemaHandler
{
    /**
     * Build structured data for a given post.
     *
     * @param array   $markup Existing markup (unused).
     * @param WP_Post $post   Post object.
     * @return array
     */
    public function schemaHandler($markup, $post) : array
    {
        // Guard conditions.
        if (is_admin() || empty($post) || !isset($post->ID)) {
            return $markup;
        }
        $postType = get_post_type($post);
        if (empty($postType) || $postType === 'product') {
            return $markup;
            // Skip WooCommerce products.
        }
        // Temporarily remove Divi filter
        $divi_callback = 'et_theme_builder_wc_set_review_metadata';
        $divi_removed = \false;
        if (\function_exists('has_filter')) {
            $priority = \has_filter('get_comment_metadata', $divi_callback);
            if ($priority !== \false) {
                remove_filter('get_comment_metadata', $divi_callback, (int) $priority);
                $divi_removed = \true;
            }
        }
        // Define schema type mappings.
        $schemaTypeMap = ['post' => 'BlogPosting', 'page' => 'Article', 'job' => 'JobPosting', 'job_listing' => 'JobPosting', 'book' => 'Book', 'movie' => 'Movie', 'event' => 'Event', 'recipe' => 'Recipe', 'course' => 'Course', 'season' => 'CreativeWorkSeason', 'series' => 'CreativeWorkSeries', 'software' => 'SoftwareApplication', 'application' => 'SoftwareApplication', 'app' => 'SoftwareApplication', 'music' => 'MusicRecording', 'game' => 'Game', 'howto' => 'HowTo', 'episode' => 'Episode', 'business' => 'LocalBusiness'];
        // Do not use 'CreativeWork' as a fallback for reviews, use 'BlogPosting' or 'Article' instead.
        $schemaType = $schemaTypeMap[$postType] ?? 'Article';
        $reviewableTypes = ['Book', 'Movie', 'Recipe', 'Course', 'CreativeWorkSeason', 'CreativeWorkSeries', 'SoftwareApplication', 'MusicRecording', 'MediaObject', 'Game', 'HowTo', 'Episode', 'LocalBusiness'];
        // Start with the base markup for the main item.
        $markup = ['@context' => 'https://schema.org/', '@type' => $schemaType, 'name' => $post->post_title, 'url' => get_permalink($post)];
        // Only attach review data to supported types.
        if (\in_array($schemaType, $reviewableTypes, \true)) {
            $reviews = get_comments(['post_id' => $post->ID, 'status' => 'approve', 'type__in' => ['comment', 'review']]);
            if (!empty($reviews)) {
                $reviewCount = 0;
                $averageRating = 0.0;
                $reviewItems = [];
                foreach ($reviews as $review) {
                    if (!empty($review->comment_parent)) {
                        continue;
                        // Skip replies.
                    }
                    $rating = get_comment_meta($review->comment_ID, 'rating', \true);
                    $ratingValue = $rating !== '' ? (float) $rating : null;
                    if ($ratingValue !== null && $ratingValue > 0) {
                        $reviewCount++;
                        $averageRating += $ratingValue;
                        $reviewItems[] = ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $review->comment_author], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $ratingValue], 'datePublished' => get_comment_date('c', $review), 'reviewBody' => $review->comment_content];
                    }
                }
                if ($reviewCount > 0 && $averageRating > 0) {
                    $trueAverage = \round($averageRating / $reviewCount, 1);
                    // Nest AggregateRating and individual Reviews correctly.
                    $markup['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $trueAverage, 'reviewCount' => $reviewCount];
                    $markup['review'] = $reviewItems;
                }
            }
        }
        // Restore Divi filter.
        if ($divi_removed) {
            add_filter('get_comment_metadata', $divi_callback, $priority ?? 10, 4);
        }
        return $markup;
    }
    /**
     * Outputs the schema in the page head for all eligible non-product post types.
     */
    public static function addCustomRichSchema() : void
    {
        // Bail early if not on frontend or not singular.
        if (is_admin() || !is_singular() || \function_exists('is_product') && \is_product()) {
            return;
        }
        global $post;
        if (empty($post) || !isset($post->ID)) {
            return;
        }
        $handler = new self();
        $markup = $handler->schemaHandler([], $post);
        if (!empty($markup)) {
            echo "\n<!-- ReviewX Rich Schema for {$post->post_type} -->\n";
            echo '<script type="application/ld+json">' . wp_json_encode($markup, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . '</script>';
            echo "\n<!-- /ReviewX Rich Schema -->\n";
        }
    }
}
