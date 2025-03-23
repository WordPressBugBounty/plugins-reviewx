<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewStarCountShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        $isProduct = !empty($attrs['product_id']);
        $id = $isProduct ? (int) $attrs['product_id'] : (int) $attrs['post_id'];
        $data = $this->getReviewsData($id, $isProduct);
        return View::render('storefront/shortcode/reviewsStarCount', ['title' => $attrs['title'] ?: $data['postTitle'], 'data' => $data, 'postType' => $data['postType'] ?: '']);
    }
    private function getReviewsData(int $id, bool $isProduct) : array
    {
        $post = get_post($id);
        $defaultData = ['product' => ['id' => $id], 'starCount' => 0.0, 'reviewsCount' => 0, 'domain' => ['baseDomain' => Helper::domainSupport()]];
        if (!$post || $isProduct && $post->post_type !== 'product' || !$isProduct && $post->post_type === 'product') {
            return $defaultData;
        }
        $starCount = 0.0;
        $reviewsCount = 0;
        if ($isProduct) {
            // WooCommerce product meta
            $starCount = get_post_meta($id, '_wc_average_rating', \true);
            $reviewsCount = get_post_meta($id, '_wc_review_count', \true);
        }
        if (!$isProduct) {
            // Custom meta for non-products
            $starCount = get_post_meta($id, 'rvx_avg_rating', \true);
            $reviewsCount = $post->comment_count;
        }
        return \array_merge($defaultData, ['starCount' => (float) $starCount ?? 0.0, 'reviewsCount' => (int) $reviewsCount ?? 0, 'postTitle' => $post ? $post->post_title : \false, 'postType' => $post ? $post->post_type : '']);
    }
}
