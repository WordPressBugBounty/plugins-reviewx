<?php

namespace Rvx\Handlers;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
use Rvx\Form\ReviewFormHelper;
class CommentBoxHandle
{
    protected $reviewFormHelper;
    public function __construct()
    {
        $this->reviewFormHelper = new ReviewFormHelper();
    }
    public function __invoke() : void
    {
        $attributes = $this->setRvxAttributes();
        $formData = $this->reviewFormHelper->builderCustomizedFormTextsData();
        $wooVerificationRequired = get_option('woocommerce_review_rating_verification_required') === 'yes';
        $requireSignIn = (bool) get_option('comment_registration');
        $currentUrl = esc_url(add_query_arg([], wp_unslash($_SERVER['REQUEST_URI'])));
        $this->reviewFormHelper->commentBoxDefaultStyleForCustomPostType();
        View::output('storefront/widget/index', ['data' => \json_encode($attributes, \JSON_UNESCAPED_UNICODE), 'formLevelData' => $formData, 'wooVerificationRequired' => $wooVerificationRequired, 'isVerifiedCustomer' => $attributes['userInfo']['isVerified'], 'requireSignIn' => $requireSignIn, 'user_is_logged_in' => $attributes['userInfo']['isLoggedIn'], 'login_url' => wp_login_url($currentUrl), 'register_url' => wp_registration_url(), 'registration_enabled' => (bool) get_option('users_can_register')]);
    }
    private function setRvxAttributes() : array
    {
        $wpCurrentUser = Helper::getWpCurrentUser();
        $userId = get_current_user_id();
        // WooCommerce verification
        if (\class_exists('WooCommerce') && is_singular('product') && get_post_type() === 'product') {
            $userInfo['isVerified'] = Helper::verifiedCustomer($userId);
        }
        $isVerifiedCustomer = 0;
        if (\class_exists('WooCommerce') && is_singular('product') && 'product' === get_post_type()) {
            $isVerifiedCustomer = Helper::verifiedCustomer($userId);
        }
        // Post type enable check
        $enabledPostTypes = $this->reviewFormHelper->rvxEnabledPostTypes();
        $currentPostType = \strtolower(get_post_type());
        $postTypeEnabled = !empty($enabledPostTypes[$currentPostType]) && \strtolower($enabledPostTypes[$currentPostType]) === $currentPostType;
        return ['postTypeEnabled' => $postTypeEnabled, 'product' => ['id' => get_the_ID(), 'postType' => $currentPostType], 'userInfo' => ['isLoggedIn' => Helper::loggedIn(), 'id' => $wpCurrentUser ? (int) $wpCurrentUser->ID : 0, 'name' => $wpCurrentUser && $wpCurrentUser->display_name ? $wpCurrentUser->display_name : '', 'email' => $wpCurrentUser && $wpCurrentUser->user_email ? $wpCurrentUser->user_email : '', 'isVerified' => $isVerifiedCustomer], 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
    }
}
