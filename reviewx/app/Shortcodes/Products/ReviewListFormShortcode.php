<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Helper;
use Rvx\Form\ReviewFormHelper;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Contracts\ShortcodeContract;
use Rvx\WPDrill\Facades\View;
class ReviewListFormShortcode implements ShortcodeContract
{
    protected ReviewFormHelper $reviewFormHelper;
    public function render(array $attrs, ?string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'post_id' => null, 'product_id' => null, 'graph' => 'off', 'filter' => 'off', 'list' => 'off', 'form' => 'on', 'designer_id' => null], $attrs);
        // Validate attributes (early exit on errors)
        if ($error = $this->validateAttributes($attrs)) {
            return $error;
        }
        $this->reviewFormHelper = new ReviewFormHelper();
        $data = $this->attributesData($attrs);
        if (!$data['postTypeEnabled']) {
            return $this->error('This post type isn\'t enabled in ReviewX.');
        }
        if (empty($data['product'])) {
            return $this->error('Unable to determine the `post type` and `id`. Please provide one parameter `post_id` or `product_id`.');
        }
        $attributes = $this->sanitizeAttributes($attrs);
        $title = $this->resolveTitle($attrs['title'], $data['product']['postTitle']);
        $formData = $this->reviewFormHelper->builderCustomizedFormTextsData();
        return View::render('storefront/shortcode/reviewListForm', ['title' => $title, 'data' => wp_json_encode($data), 'attributes' => $attributes, 'formLevelData' => $formData, 'wooVerificationRequired' => 'yes' === get_option('woocommerce_review_rating_verification_required'), 'isVerifiedCustomer' => $data['userInfo']['isVerified'], 'requireSignIn' => (bool) get_option('comment_registration'), 'user_is_logged_in' => $data['userInfo']['isLoggedIn'], 'login_url' => wp_login_url(esc_url(add_query_arg([], wp_unslash($_SERVER['REQUEST_URI'])))), 'register_url' => wp_registration_url(), 'registration_enabled' => (bool) get_option('users_can_register')]);
    }
    private function error(string $message) : string
    {
        return '<div class="warning notice notice-error"><b>Error:</b> ' . esc_html($message) . '</div>';
    }
    private function validateAttributes(array $attrs) : ?string
    {
        if (!Client::getSync()) {
            return $this->error('Please complete the synchronization process of ReviewX.');
        }
        if ($attrs['graph'] === 'off' && $attrs['list'] === 'off' && $attrs['form'] === 'off') {
            return $this->error('Please provide one parameter (graph, list, form) value as `on` in the shortcode.');
        }
        if (!empty($attrs['post_id']) && !empty($attrs['product_id'])) {
            return $this->error('Please provide either `post_id` or `product_id`. Both are not supported.');
        }
        return null;
    }
    private function sanitizeAttributes(array $attrs) : array
    {
        $attributes = [];
        foreach (['graph', 'filter', 'list', 'form'] as $toggle) {
            $attributes[$toggle] = !empty($attrs[$toggle]) ? esc_attr($attrs[$toggle]) : 'off';
        }
        $attributes['designer_id'] = $attrs['designer_id'] ? esc_attr($attrs['designer_id']) : null;
        return $attributes;
    }
    private function resolveTitle(?string $attrTitle, string $postTitle) : string
    {
        if ($attrTitle === 'false') {
            return 'false';
        }
        if ($attrTitle === 'true' || empty($attrTitle)) {
            return $postTitle;
        }
        return esc_html($attrTitle);
    }
    private function attributesData(array $attrs) : array
    {
        global $post;
        $type = [];
        // Case 1: If shortcode explicitly provides post_id or product_id
        if (!empty($attrs['post_id']) || !empty($attrs['product_id'])) {
            $id = !empty($attrs['post_id']) ? (int) $attrs['post_id'] : (int) $attrs['product_id'];
            $postObj = get_post($id);
            $type = ['id' => $id, 'postType' => $postObj ? \strtolower($postObj->post_type) : 'rvx_no_post_type', 'postTitle' => $postObj ? esc_html(get_the_title($postObj)) : ''];
        }
        // Case 2: If no ID provided, use current singular post
        if (is_singular() && empty($attrs['post_id']) && empty($attrs['product_id'])) {
            $type = ['id' => $post ? (int) $post->ID : 0, 'postType' => $post && $post->post_type ? \strtolower($post->post_type) : 'rvx_no_post_type', 'postTitle' => $post && $post->post_title ? esc_html($post->post_title) : ''];
        }
        // Validate if post type is enabled in ReviewX
        $enabledPostTypes = $this->reviewFormHelper->rvxEnabledPostTypes();
        $currentPostType = $type['postType'] ?? null;
        $postTypeEnabled = isset($currentPostType, $enabledPostTypes[$currentPostType]) && \strtolower($enabledPostTypes[$currentPostType]) === $currentPostType;
        // User info
        $userId = get_current_user_id();
        $wpCurrentUser = Helper::getWpCurrentUser();
        $isVerifiedCustomer = 0;
        if (\class_exists('WooCommerce') && is_singular('product') && 'product' === get_post_type()) {
            $isVerifiedCustomer = Helper::verifiedCustomer($userId);
        }
        return ['postTypeEnabled' => $postTypeEnabled, 'product' => $type, 'userInfo' => ['isLoggedIn' => Helper::loggedIn(), 'id' => $wpCurrentUser ? (int) $wpCurrentUser->ID : 0, 'name' => $wpCurrentUser && $wpCurrentUser->display_name ? $wpCurrentUser->display_name : '', 'email' => $wpCurrentUser && $wpCurrentUser->user_email ? $wpCurrentUser->user_email : '', 'isVerified' => $isVerifiedCustomer], 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
    }
}
