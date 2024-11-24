<?php

namespace Rvx\Handlers\RichSchma;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
use Rvx\Utilities\Helper;
class WoocommerceRichSchmaHandler
{
    public function __invoke($markup, $product)
    {
        $average_rating = $product->get_average_rating();
        $review_count = $product->get_review_count();
        if ($review_count > 0) {
            $markup['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $average_rating, 'reviewCount' => $review_count];
        }
        $reviews = get_comments(['post_id' => $product->get_id(), 'status' => 'approve', 'type' => 'review']);
        if (!empty($reviews)) {
            $markup['review'] = [];
            foreach ($reviews as $review) {
                $markup['review'][] = ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $review->comment_author], 'reviewRating' => ['@type' => 'Rating', 'ratingValue' => get_comment_meta($review->comment_ID, 'rating', \true)], 'datePublished' => get_comment_date('c', $review), 'description' => $review->comment_content];
            }
        }
        if (is_product() && Helper::reviewSettings()['reviews']['product_schema'] === \false) {
            unset($markup['aggregateRating']);
            unset($markup['review']);
        }
        return $markup;
    }
}
