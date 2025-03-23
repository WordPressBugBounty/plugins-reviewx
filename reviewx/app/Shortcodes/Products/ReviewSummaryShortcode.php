<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Api\ReviewsApi;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewSummaryShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        // Define default attributes to accept both product_id and post_id.
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        // If both IDs are provided, return an error.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine the type and set the ID.
        $isProduct = !empty($attrs['product_id']);
        $id = $isProduct ? (int) $attrs['product_id'] : (int) $attrs['post_id'];
        // Prepare the data.
        $data = $this->productWiseReviewShow($id, $isProduct);
        return View::render('storefront/shortcode/reviewSummary', ['title' => $attrs['title'] ?: $data['postTitle'], 'data' => \json_encode($data)]);
    }
    /**
     * Build the data structure for the review summary.
     *
     * @param int  $id
     * @param bool $isProduct
     * @return string JSON encoded attributes.
     */
    public function productWiseReviewShow($id, $isProduct) : array
    {
        $post = get_post($id);
        $attributes = ['product' => ['id' => $id], 'postTitle' => $post ? $post->post_title : \false, 'postType' => $post ? $post->post_type : '', 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return $attributes;
    }
}
