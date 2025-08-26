<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewListShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null, 'post_type' => null, 'rating' => null, 'per_page' => null, 'sort_by' => null], $attrs);
        // if (empty($attrs['product_id']) && empty($attrs['post_id'])) {
        //     $post_type = $attrs['post_type'];
        //     $rating = $attrs['rating'];
        //     $per_page = $attrs['per_page'];
        //     $sort_by = $attrs['sort_by'];
        //     $data =  $this->siteAllReviewShow($post_type, $rating, $per_page, $sort_by);
        //     return View::render('storefront/shortcode/allReview', [
        //         'title' => $attrs['title'] ?: false,
        //         'data'  => json_encode($data),
        //     ]);
        // }
        // If both product_id and post_id are provided, return an error.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine the type and select the appropriate IDs.
        $isProduct = !empty($attrs['product_id']);
        $idsValue = $isProduct ? $attrs['product_id'] : $attrs['post_id'];
        // Process the IDs: split by commas if provided.
        $idArray = [];
        if ($idsValue) {
            $idArray = \array_map('trim', \explode(',', $idsValue));
        }
        // Prepare the data to be sent to the view.
        $data = $this->productWiseReviewShow($idArray, $isProduct);
        return View::render('storefront/shortcode/reviewList', ['title' => $attrs['title'] ?: \false, 'data' => \json_encode($data)]);
    }
    public function productWiseReviewShow($ids, $isProduct) : array
    {
        $attributes = ['ids' => $ids, 'type' => $isProduct ? 'product' : 'post', 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return $attributes;
    }
    public function siteAllReviewShow($post_type, $rating, $per_page, $sort_by) : array
    {
        $attributes = ['post_type' => $post_type, 'rating' => $rating, 'per_page' => $per_page, 'sort_by' => $sort_by, 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return $attributes;
    }
}
