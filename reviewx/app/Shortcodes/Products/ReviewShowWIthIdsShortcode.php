<?php

namespace Rvx\Shortcodes\Products;

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
            return View::render('shortcode/reviewShowWIthIds', ['title' => $attrs['title'], 'data' => $attributes]);
        }
        return '';
    }
    public function reviewsWiseReviewShow($reviewsIds)
    {
        $reviewIdsJson = \json_encode($reviewsIds);
        return $reviewIdsJson;
        /** 
        // Structure the data for the JavaScript window object
        echo '
        <script>
            window.__rvx_attributes__ = {
            ...window.__rvx_attributes__,
            shortCodes:{
            ...window.__rvx_attributes__.shortCodes,
                    rvx_reviews: '. json_encode($reviewsIds) . '
                }
            };
        </script>
        ';
        */
    }
}
