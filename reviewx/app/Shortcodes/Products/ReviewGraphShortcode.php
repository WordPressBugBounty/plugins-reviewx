<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewGraphShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        // Check if both product_id and post_id are provided.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine whether we're dealing with a product or a post.
        $isProduct = !empty($attrs['product_id']);
        $id = $isProduct ? (int) $attrs['product_id'] : (int) $attrs['post_id'];
        $data = $this->productWiseReviewShow($id, $isProduct);
        return View::render('storefront/shortcode/reviewGraph', ['title' => $attrs['title'] ?: $data['postTitle'], 'data' => \json_encode($data)]);
    }
    public function productWiseReviewShow($id, bool $isProduct) : array
    {
        $post = get_post($id);
        $attributes = ['product' => ['id' => $id], 'postTitle' => $post ? $post->post_title : \false, 'postType' => $post ? $post->post_type : '', 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return $attributes;
    }
}
