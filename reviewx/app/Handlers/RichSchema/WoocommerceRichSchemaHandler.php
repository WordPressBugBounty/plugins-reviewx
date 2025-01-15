<?php

namespace Rvx\Handlers\RichSchema;

use Rvx\Services\SettingService;
use Rvx\WC_Product;
class WoocommerceRichSchemaHandler
{
    /**
     * Process and add rich schema data to WooCommerce product markup.
     *
     * @param array      $markup   Existing schema markup.
     * @param WC_Product $product  WooCommerce product object.
     * @return array Updated schema markup.
     */
    public function __invoke($markup, $product) : array
    {
        $averageRating = $product->get_average_rating();
        $reviewCount = $product->get_review_count();
        // Add aggregate rating if reviews exist.
        if ($reviewCount > 0) {
            $markup['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $averageRating, 'reviewCount' => $reviewCount];
        }
        // Fetch approved reviews for the product.
        $reviews = get_comments(['post_id' => $product->get_id(), 'status' => 'approve', 'type' => 'review']);
        if (!empty($reviews)) {
            $markup['review'] = [];
            foreach ($reviews as $review) {
                $markup['review'][] = ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $review->comment_author], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => get_comment_meta($review->comment_ID, 'rating', \true)], 'datePublished' => get_comment_date('c', $review), 'description' => $review->comment_content];
            }
        }
        // Remove schema elements based on settings.
        if (is_product() && (new SettingService())->getReviewSettings()['reviews']['product_schema'] === \true) {
            unset($markup['aggregateRating'], $markup['review']);
        }
        return $markup;
    }
}
