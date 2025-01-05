<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewShowWIthIdsShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'review_ids' => null], $attrs);
        $reviewsIds = $attrs['review_ids'];
        $productIdArray = [];
        // If review_ids is not null, process it
        if ($reviewsIds) {
            // Split review_ids by commas and trim whitespace
            $productIdArray = \array_map('trim', \explode(',', $reviewsIds));
            // Send multiple product IDs to the JavaScript variable
            $attributes = $this->reviewsWiseReviewShow($productIdArray);
            // Return the view (empty or simplified, as no review data is required)
            return View::render('storefront/shortcode/reviewShowWIthIds', ['title' => $attrs['title'], 'data' => $attributes]);
        }
        return '';
    }
    public function reviewsWiseReviewShow($reviewsIds)
    {
        $reviewIdsJson = [$reviewsIds, 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return \json_encode($reviewIdsJson);
    }
}
