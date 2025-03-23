<?php

namespace Rvx\Shortcodes;

use Rvx\Api\ReviewsApi;
use Rvx\WPDrill\Facades\View;
use Rvx\Api\GoogleReviewApi;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class GoogleReviewLIst implements ShortcodeContract
{
    protected $googlereview;
    public function __construct()
    {
        $this->googlereview = new GoogleReviewApi();
    }
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'id' => null, 'truncate' => null, 'limit' => null, 'loadmore' => null, 'cache' => null], $attrs);
        $reviews = $this->review($attrs['cache']);
        return View::render('storefront/shortcode/googleReviewList', [
            'title' => $attrs['title'] ?: 'Google Reviews',
            'content' => $reviews,
            'reviewLimit' => (int) $attrs['limit'] ?: 3,
            'loadMore' => !empty($attrs['loadmore']) ? $attrs['loadmore'] : \true,
            // true to enable, false to disable
            'truncateLimit' => !empty($attrs['truncate']) ? $attrs['truncate'] : 300,
        ]);
    }
    public function review($cache_time)
    {
        // Check cache first
        $cached_reviews = get_transient('google_reviews_cache');
        if ($cached_reviews !== \false) {
            return $cached_reviews;
        }
        // If no cache, fetch from API
        $rev = $this->googlereview->googleReviewGet();
        $data = \json_decode($rev, \true);
        // Cache for 1 day (86400 seconds)
        $cache_duration = (int) $cache_time ?: 86400;
        set_transient('google_reviews_cache', $data['data'], $cache_duration);
        return $data['data'];
    }
}
