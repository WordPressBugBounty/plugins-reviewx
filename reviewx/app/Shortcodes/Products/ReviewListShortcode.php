<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewListShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null], $attrs);
        $productIds = $attrs['product_id'];
        $productIdArray = [];
        // If product_id is not null, process it
        if ($productIds) {
            // Split product_id by commas and trim whitespace
            $productIdArray = \array_map('trim', \explode(',', $productIds));
        }
        // Send multiple product IDs to the JavaScript variable
        $attributes = $this->productWiseReviewShow($productIdArray);
        // Return the view (empty or simplified, as no review data is required)
        return View::render('storefront/shortcode/reviewList', ['title' => $attrs['title'], 'data' => $attributes]);
    }
    public function productWiseReviewShow($productIds)
    {
        $productIdsJson = [$productIds, 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return \json_encode($productIdsJson);
    }
}
