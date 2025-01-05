<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewsStarCountShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        $attributes = $this->productWiseStarCountShow($attrs);
        return View::render('storefront/shortcode/reviewsStarCount', ['title' => $attrs['title'], 'data' => $attributes]);
    }
    private function productWiseStarCountShow(array $attrs) : array
    {
        $data = $this->getProductReviewsStarCount($attrs);
        if (!empty($attrs['product_id'])) {
            return ['product' => ['id' => $attrs['product_id']], 'starCount' => $data['starCount'], 'reviewsCount' => $data['reviewsCount'], 'domain' => ['baseDomain' => Helper::domainSupport()]];
        }
        if (!empty($attrs['post_id']) && empty($attrs['product_id'])) {
            return ['product' => ['id' => $attrs['post_id']], 'starCount' => $data['starCount'], 'reviewsCount' => $data['reviewsCount'], 'domain' => ['baseDomain' => Helper::domainSupport()]];
        }
        return ['product' => ['id' => 0], 'starCount' => 0, 'reviewsCount' => 0, 'domain' => ['baseDomain' => Helper::domainSupport()]];
    }
    private function getProductReviewsStarCount(array $attrs) : array
    {
        $starCount = get_post_meta((int) $attrs['product_id'], '_wc_average_rating', \true);
        $reviewsCount = get_post_meta((int) $attrs['product_id'], '_wc_review_count', \true);
        return ['starCount' => (float) $starCount ?? (float) 0.0, 'reviewsCount' => (int) $reviewsCount ?? (int) 0];
    }
}
