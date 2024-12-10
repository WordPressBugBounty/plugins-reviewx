<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Api\ReviewsApi;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewSummaryShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null], $attrs);
        $productId = $attrs['product_id'];
        $attributes = $this->productWiseReviewShow($productId);
        return View::render('storefront/shortcode/reviewSummary', ['title' => $attrs['title'], 'data' => $attributes]);
    }
    public function productWiseReviewShow($productId)
    {
        $attributes = ['product' => ['id' => $productId]];
        return \json_encode($attributes);
        /**
                echo '<script>
                window.__rvx_attributes__ = ' . json_encode($attributes) . ';
            </script>';
        */
    }
}
