<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewShowWIthIdsShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'review_ids' => null], $attrs);
        // If review_ids is not provided, return an error.
        if (empty($attrs['review_ids'])) {
            return '<div class="warning">Error: Please provide "review_ids" in the shortcode.</div>';
        }
        $reviewsIds = $attrs['review_ids'];
        $productIdArray = [];
        // If review_ids is not null, process it
        if ($reviewsIds) {
            // Split review_ids by commas and trim whitespace
            $productIdArray = \array_map('trim', \explode(',', $reviewsIds));
            // Send multiple product IDs to the JavaScript variable
            $data = $this->reviewsWiseReviewShow($productIdArray);
            // Return the view (empty or simplified, as no review data is required)
            return View::render('storefront/shortcode/reviewShowWIthIds', ['title' => $attrs['title'] ?: \false, 'data' => \json_encode($data)]);
        }
        return '';
    }
    public function reviewsWiseReviewShow($reviewsIds) : array
    {
        $attributes = ['ids' => $reviewsIds, 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return $attributes;
    }
}
