<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewStatshortcode implements ShortcodeContract
{
    public function render(array $attrs, ?string $content = null) : string
    {
        // Set default attributes to include both product_id and post_id.
        $attrs = shortcode_atts(['title' => null, 'product_id' => null, 'post_id' => null], $attrs);
        // Check if both product_id and post_id are provided.
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        // If both product_id and post_id are provided, return an error.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning">Error: Please use only one of "product_id" or "post_id" in the shortcode.</div>';
        }
        // Determine whether this is for a product or a post.
        $isProduct = !empty($attrs['product_id']);
        $id = $isProduct ? (int) $attrs['product_id'] : (int) $attrs['post_id'];
        // Retrieve data related to review stats.
        $data = $this->getReviewStatsData($id, $isProduct);
        // Title handling
        if ($attrs['title'] === 'false') {
            $title = 'false';
        } elseif ($attrs['title'] === 'true' || empty($attrs['title'])) {
            $title = $data['postTitle'];
        } else {
            $title = esc_html($attrs['title']);
        }
        // If no title is provided, use the post title from the data.
        return View::render('storefront/shortcode/reviewStats', ['title' => $title, 'postType' => !empty($data['postType']) ? $data['postType'] : '', 'data' => \json_encode($data)]);
    }
    /**
     * Retrieve review stats data based on an ID and its type (product or post).
     *
     * @param int  $id
     * @param bool $isProduct
     *
     * @return array
     */
    public function getReviewStatsData(int $id, bool $isProduct) : array
    {
        // Retrieve the post object.
        $post = get_post($id);
        $defaultData = ['product' => ['id' => $id], 'postTitle' => $post ? $post->post_title : \false, 'postType' => $post ? $post->post_type : '', 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return $defaultData;
    }
}
