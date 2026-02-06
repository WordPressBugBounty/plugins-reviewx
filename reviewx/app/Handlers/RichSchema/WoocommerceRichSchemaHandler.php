<?php

namespace Rvx\Handlers\RichSchema;

use Rvx\Services\SettingService;
use Rvx\WC_Product;
/**
 * Handles rich schema markup for WooCommerce single product pages.
 */
class WoocommerceRichSchemaHandler
{
    /**
     * Adds or modifies structured data for WooCommerce product schema.
     *
     * @param array      $markup   Existing WooCommerce schema data.
     * @param WC_Product $product  WooCommerce product object.
     * @return array
     */
    public function __invoke($markup, $product) : array
    {
        // Ensure we are on a single product page and in frontend.
        if (is_admin() || !\function_exists('is_product') || !\is_product()) {
            return $markup;
        }
        if (!$product instanceof WC_Product) {
            return $markup;
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
        // Allow disabling via settings.
        $settings = (new SettingService())->getReviewSettings('product');
        if (!empty($settings['reviews']['product_schema']) && $settings['reviews']['product_schema'] == 1) {
            if (!empty($markup['aggregateRating'])) {
                unset($markup['aggregateRating']);
            }
            if (!empty($markup['review'])) {
                unset($markup['review']);
            }
            return $markup;
        }
        // Get product rating data.
        // $averageRating = (float) $product->get_average_rating();
        // $reviewCount   = (int) $product->get_review_count();
        $reviewCount = 0;
        $averageRating = 0.0;
        $reviewItems = [];
        // Fetch all approved reviews.
        $reviews = get_comments(['post_id' => $product->get_id(), 'status' => 'approve', 'type' => 'review']);
        if (!empty($reviews)) {
            foreach ($reviews as $review) {
                if (!empty($review->comment_parent)) {
                    continue;
                    // Skip replies
                }
                $rating = get_comment_meta($review->comment_ID, 'rating', \true);
                $ratingValue = $rating !== '' ? (float) $rating : null;
                if ($ratingValue !== null) {
                    $reviewCount++;
                    $averageRating += $ratingValue;
                    $reviewItems[] = ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $review->comment_author], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $ratingValue], 'datePublished' => get_comment_date('c', $review), 'reviewBody' => $review->comment_content];
                }
            }
        }
        if ($reviewCount > 0 && $averageRating > 0) {
            $trueAverage = \round($averageRating / $reviewCount, 2);
            $markup['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $trueAverage, 'reviewCount' => $reviewCount];
            $markup['review'] = $reviewItems;
        }
        // Restore Divi filter
        if ($divi_removed) {
            add_filter('get_comment_metadata', $divi_callback, $priority ?? 10, 4);
        }
        return $markup;
    }
}
