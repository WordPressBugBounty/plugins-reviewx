<?php

namespace Rvx\Shortcodes\Products;

use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewGraphShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null], $attrs);
        $productId = $attrs['product_id'];
        $attributes = $this->productWiseReviewShow($productId);
        return View::render('shortcode/reviewGraph', ['title' => $attrs['title'], 'data' => $attributes]);
    }
    public function productWiseReviewShow($productId)
    {
        $attributes = ['product' => ['id' => $productId]];
        return \json_encode($attributes);
        /**
         * echo '<script>
         * window.parent.__rvx_attributes__.shortCodes.reviewGraph = ' . $attributesJson . ';
         * </script>';
         *
         * echo '<script>
         * window.__rvx_attributes__ = {
         * ...window.__rvx_attributes__,
         * shortCodes: {
         * ...window.__rvx_attributes__.shortCodes,
         * rvx_review_graph: '.$attributesJson.'
         * }
         * }
         * </script>';
         */
    }
}
