<?php

namespace Rvx\Shortcodes;

use Rvx\Api\ReviewsApi;
use Rvx\WPDrill\Facades\View;
use Rvx\Api\GoogleReviewApi;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class GoogleReviewLIst implements ShortcodeContract
{
    public function review()
    {
        $review = new GoogleReviewApi();
        $rev = $review->googleReviewGet();
        $data = \json_decode($rev, \true);
        return $data['data'];
    }
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null], $attrs);
        $reviews = $this->review();
        return View::render('storefront/shortcode/googleReviewList', ['content' => $reviews]);
    }
}
