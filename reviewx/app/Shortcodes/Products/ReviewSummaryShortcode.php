<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewSummaryShortcode implements ShortcodeContract
{
    public function render(array $attrs, ?string $content = null) : string
    {
        // Define default attributes to accept both product_id and post_id.
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        // Check if both product_id and post_id are provided.
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        // If both IDs are provided, return an error.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine the type and set the ID.
        $isProduct = !empty($attrs['product_id']);
        $id = $isProduct ? (int) $attrs['product_id'] : (int) $attrs['post_id'];
        // Prepare the data.
        $data = $this->productWiseReviewShow($id, $isProduct);
        // Title handling
        if ($attrs['title'] === 'false') {
            $title = 'false';
        } elseif ($attrs['title'] === 'true' || empty($attrs['title'])) {
            $title = $data['postTitle'];
        } else {
            $title = esc_html($attrs['title']);
        }
        return View::render('storefront/shortcode/reviewSummary', ['title' => $title, 'data' => \json_encode($data)]);
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
        $attributes = ['product' => ['id' => $id], 'postTitle' => $post ? $post->post_title : \false, 'postType' => $post ? $post->post_type : '', 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return $attributes;
    }
}
