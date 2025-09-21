<?php

namespace Rvx\Shortcodes\Products;

use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\Utilities\Helper;
use Rvx\Form\ReviewFormHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Facades\View;
class ReviewListShortcode implements ShortcodeContract
{
    protected $reviewFormHelper;
    public function render(array $attrs, ?string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'post_type' => null, 'post_id' => null, 'product_id' => null, 'sort_by' => null, 'attachment' => null, 'rating' => null, 'filter' => 'on'], $attrs);
        // Check if both product_id and post_id are provided.
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        // If both product_id and post_id are provided, return an error.
        if (!empty($attrs['product_id']) && !empty($attrs['post_id'])) {
            return '<div class="warning notice notice-error"><b>Error:</b> Please use only one of `product_id` or `post_id` in the shortcode.</div>';
        }
        // If post_type provided with product_id and/or post_id, return an error
        if (!empty($attrs['post_type']) && (!empty($attrs['product_id']) || !empty($attrs['post_id']))) {
            return '<div class="warning notice notice-error"><b>Error:</b> Post type can\'t be used with `product_id` and/or `post_id` in the shortcode.</div>';
        }
        if (!empty($attrs['product_id']) || !empty($attrs['post_id'])) {
            $title = esc_attr($attrs['title']) ?: 'true';
            $type_name_id = !empty($attrs['product_id']) ? ' product_id="' . esc_attr($attrs['product_id']) . '"' : (!empty($attrs['post_id']) ? ' post_id="' . esc_attr($attrs['post_id']) . '"' : '');
            return do_shortcode('[rvx-review-form title="' . $title . '" filter="' . esc_attr($attrs['filter']) . '" ' . $type_name_id . ' graph="off" list="on" form="off"]');
        }
        if (!empty($attrs['post_type']) || empty($attrs['product_id']) && empty($attrs['post_id'])) {
            $this->reviewFormHelper = new ReviewFormHelper();
            $data = $this->attributesData($attrs);
            if ($data['post_type_enabled'] == \false && !empty(esc_attr($attrs['post_type']))) {
                return '<div class="warning notice notice-error"><b>Error:</b> This post type isn\'t enabled in ReviewX.</div>';
            }
            $title = !isset($attrs['title']) || $attrs['title'] === 'false' ? 'false' : esc_html($attrs['title']);
            return View::render('storefront/shortcode/reviewList', ['title' => $title, 'data' => $data]);
        }
        return '<div class="warning notice notice-error"><b>Error:</b> Please, make sure you have provided necessary parameter\'(s) in the shortcode. Please, follow documentation.</div>';
    }
    public function attributesData(array $attrs) : array
    {
        $postTypeEnable = 0;
        $postType = null;
        if (!empty($attrs['post_type'])) {
            $enabled_post_types = $this->reviewFormHelper->rvxEnabledPostTypes();
            $currentPostType = esc_attr($attrs['post_type']) ?: 'rvx_no_post_type';
            $postType = esc_attr($attrs['post_type']) ?: null;
            if (isset($enabled_post_types[$currentPostType]) && \strtolower($enabled_post_types[$currentPostType]) == $currentPostType) {
                $postTypeEnable = 1;
            }
        }
        $attributes = ['post_type_enabled' => $postTypeEnable, 'filter' => esc_attr($attrs['filter']) ?: 'off', 'params' => ['post_type' => $postType, 'sort_by' => esc_attr($attrs['sort_by']) ?: '', 'attachment' => esc_attr($attrs['attachment']) ?: '', 'rating' => esc_attr($attrs['rating']) ?: ''], 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return $attributes;
    }
}
