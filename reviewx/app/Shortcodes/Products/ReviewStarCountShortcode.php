<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Form\ReviewFormHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewStarCountShortcode implements ShortcodeContract
{
    public function render(array $attrs, ?string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => 'false', 'product_id' => null, 'post_id' => null, 'info' => 'default'], $attrs);
        // Ensure ReviewX sync is complete
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        $productId = !empty($attrs['product_id']) ? (int) $attrs['product_id'] : 0;
        $postId = !empty($attrs['post_id']) ? (int) $attrs['post_id'] : 0;
        // Prevent both IDs at once
        if ($productId && $postId) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine the final ID to use
        $id = $productId ?: $postId;
        // Auto-detect from global post if neither ID provided
        if (!$productId && !$postId) {
            global $post;
        } else {
            $post = get_post($id);
        }
        if (!$post) {
            return '<div class="warning">No post or product found!</div>';
        }
        $data = $this->getReviewsData($post);
        if (!$data['post_type_enabled']) {
            return '<div class="warning notice notice-error"><b>Error:</b> This post type isn\'t enabled in ReviewX.</div>';
        }
        // Title handling
        if ($attrs['title'] === 'false') {
            $title = 'false';
        } elseif ($attrs['title'] === 'true' || empty($attrs['title'])) {
            $title = $data['postTitle'];
        } else {
            $title = esc_html($attrs['title']);
        }
        return View::render('storefront/shortcode/reviewsStarCount', ['title' => $title, 'data' => $data, 'info' => $attrs['info'] ?: 'default', 'postType' => $data['postType']]);
    }
    private function getReviewsData($post) : array
    {
        $id = $post->ID ?? 0;
        // Check enabled post types even if $post is null
        $enabledPostTypes = (new ReviewFormHelper())->rvxEnabledPostTypes();
        $postTypeEnabled = 0;
        if ($post) {
            $postTypeEnabled = isset($enabledPostTypes[\strtolower($post->post_type)]) ? 1 : 0;
        }
        // Default data
        $defaultData = ['product' => ['id' => $id, 'post' => $id], 'starCount' => 0.0, 'reviewsCount' => 0, 'post_type_enabled' => $postTypeEnabled, 'postTitle' => $post->post_title ?? '', 'postType' => $post->post_type ?? '', 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        if (!$post) {
            return $defaultData;
        }
        // Get actual star/review counts
        if ($post->post_type === 'product') {
            $defaultData['starCount'] = (float) get_post_meta($id, '_wc_average_rating', \true);
            $defaultData['reviewsCount'] = (int) get_post_meta($id, '_wc_review_count', \true);
        } else {
            $defaultData['starCount'] = (float) get_post_meta($id, 'rvx_avg_rating', \true);
            $defaultData['reviewsCount'] = (int) $post->comment_count;
        }
        return $defaultData;
    }
}
