<?php

namespace Rvx\Shortcodes;

use Rvx\Api\GoogleReviewApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
use Rvx\Services\GoogleReviewService;
class GoogleReviewLIst implements ShortcodeContract
{
    protected $googleReview;
    protected $googleReviewService;
    public function __construct()
    {
        $this->googleReview = new GoogleReviewApi();
        $this->googleReviewService = new GoogleReviewService();
    }
    public function render(array $attrs, ?string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'id' => null, 'truncate' => '300', 'limit' => '3', 'loadmore' => 'false', 'cache' => null], $attrs);
        // Check if both product_id and post_id are provided.
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        // Fetch cached/validated API credentials
        $credentials = $this->getGoogleApiCredentials();
        // Bail out early if invalid
        if (empty($credentials['google_api_key']) || empty($credentials['google_place_id_or_url'])) {
            return '<div class="warning">Error: Please configure your Google API Key and Place ID/URL in ReviewX.</div>';
        }
        // Fetch reviews
        $reviews = $this->reviews($attrs['cache'], $credentials);
        $title = isset($attrs['title']) && $attrs['title'] === 'false' ? 'false' : ($attrs['title'] === 'true' || empty($attrs['title']) ? 'Google Reviews' : esc_html($attrs['title']));
        return View::render('storefront/shortcode/googleReviewList', ['title' => $title, 'content' => $reviews, 'reviewLimit' => (int) $attrs['limit'] ?: 3, 'loadMore' => !empty($attrs['loadmore']) ? $attrs['loadmore'] : \true, 'truncateLimit' => !empty($attrs['truncate']) ? $attrs['truncate'] : 300]);
    }
    protected function reviews($cache_time, array $credentials)
    {
        $place_id = $credentials['google_place_id_or_url'];
        $cache_key = 'rvx_google_reviews_cache_' . \md5($place_id);
        $cached_reviews = \get_transient($cache_key);
        if ($cached_reviews !== \false) {
            return $cached_reviews;
        }
        $reviews = $this->googleReview->googleReviewGet();
        $reviews = \json_decode($reviews, \true);
        $cache_duration = (int) $cache_time ?: DAY_IN_SECONDS;
        set_transient($cache_key, $reviews['data'], $cache_duration);
        if (empty($reviews['data']['reviews'])) {
            return [];
        }
        return $reviews['data'];
    }
    /**
     * Get and cache Google API credentials for 1 hour.
     *
     * @return array
     */
    protected function getGoogleApiCredentials() : array
    {
        $cached = \get_transient('rvx_google_api_settings');
        if ($cached !== \false) {
            return $cached;
        }
        // Already returns array
        $response = $this->googleReviewService->googleReviewPlaceApi();
        $response = \json_decode($response, \true);
        $response = $response['data']['creadential']['google_place'];
        set_transient('rvx_google_api_settings', $response, HOUR_IN_SECONDS);
        if (empty($response['google_api_key']) || empty($response['google_place_id_or_url'])) {
            return [];
        }
        return $response;
    }
}
