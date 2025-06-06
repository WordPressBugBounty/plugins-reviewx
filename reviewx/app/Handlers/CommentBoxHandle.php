<?php

namespace Rvx\Handlers;

use Rvx\CPT\CptHelper;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
class CommentBoxHandle
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function __invoke() : void
    {
        $attributes = $this->setRvxAttributes();
        $formData = $this->builderCustomizedFormTextsData();
        $requireSignIn = get_option('comment_registration');
        $userIsLoggedIn = is_user_logged_in();
        $currentUrl = esc_url(add_query_arg([], wp_unslash($_SERVER['REQUEST_URI'])));
        $loginUrl = wp_login_url($currentUrl);
        $registerUrl = wp_registration_url();
        $registrationEnabled = get_option('users_can_register');
        $this->commentBoxDefaultStyleForCustomPostType();
        View::output('storefront/widget/index', ['data' => $attributes, 'formLevelData' => $formData, 'requireSignIn' => $requireSignIn, 'user_is_logged_in' => $userIsLoggedIn, 'login_url' => $loginUrl, 'register_url' => $registerUrl, 'registration_enabled' => $registrationEnabled]);
    }
    public function setRvxAttributes()
    {
        $user_id = get_current_user_id();
        $wpCurrentUser = Helper::getWpCurrentUser();
        if (\class_exists('WooCommerce') && is_singular() && 'product' === get_post_type()) {
            $is_verified_customer = Helper::verifiedCustomer($user_id);
        } else {
            $is_verified_customer = 0;
        }
        $user_name = $wpCurrentUser ? $wpCurrentUser->display_name : '';
        $attributes = ['product' => ['id' => get_the_ID(), 'postType' => \strtolower(get_post_type())], 'userInfo' => ['isLoggedIn' => Helper::loggedIn(), 'id' => $wpCurrentUser ? $wpCurrentUser->ID : null, 'name' => $user_name, 'email' => $wpCurrentUser ? $wpCurrentUser->user_email : '', 'isVerified' => $is_verified_customer], 'domain' => ['baseDomain' => Helper::domainSupport()]];
        return \json_encode($attributes);
    }
    public function commentBoxDefaultStyleForCustomPostType() : void
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        if (!is_singular($enabled_post_types)) {
            ?>
            <style>
                #rvx-storefront-widget {
                    display: none;
                }
            </style>
            <?php 
        }
    }
    public function builderCustomizedFormTextsData()
    {
        // Helper function to extract specific values from the decoded Oxygen Builder data
        $builder_status_data = $this->builderStatusData();
        //
        if ($builder_status_data[0] === \true) {
            if ($builder_status_data[1] === 'elementor') {
                // Elementor builder is active
                global $builderElementorSetting;
                if (!empty($builderElementorSetting)) {
                    return \json_encode($builderElementorSetting, \JSON_UNESCAPED_UNICODE);
                } else {
                    return $this->rvxDefaultReviewFormLevelData();
                }
            } elseif ($builder_status_data[1] === 'oxygen') {
                // Oxygen builder is active
                return $this->builderOxygenReviewFormLevelData();
            } elseif ($builder_status_data[1] === 'divi') {
                // Divi builder is active
                // Not yet developed the text fields for divi builder
                // So for now, return default review form level data
                return $this->rvxDefaultReviewFormLevelData();
            }
        }
        return $this->rvxDefaultReviewFormLevelData();
    }
    private function builderOxygenReviewFormLevelData()
    {
        // SQL query to fetch the post_id based on meta_key and meta_value criteria
        global $wpdb;
        $query = "\n            SELECT post_id \n            FROM {$wpdb->prefix}postmeta \n            WHERE meta_key = '_ct_builder_json' \n            AND meta_value LIKE '%oxy-reviewx-product-tabs_rvx_oxygen%' \n            ORDER BY post_id DESC \n            LIMIT 1\n        ";
        $post_id = $wpdb->get_var($query);
        if ($post_id) {
            // If a post_id is found, retrieve the JSON data for the Oxygen template
            $post_meta = get_post_meta($post_id, '_ct_builder_json', \true);
            // Check if post_meta is available to avoid errors when working with empty data
            if ($post_meta) {
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_write_a_review"
                $regex_text_write_a_review = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_write_a_review":"(.*?)"/';
                \preg_match($regex_text_write_a_review, $post_meta, $write_a_review);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_rating_star_title"
                $regex_text_rating_star_title = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_rating_star_title":"(.*?)"/';
                \preg_match($regex_text_rating_star_title, $post_meta, $text_rating_star_title);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_review_title"
                $regex_text_review_title = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_review_title":"(.*?)"/';
                \preg_match($regex_text_review_title, $post_meta, $text_review_title);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_title"
                $regex_placeholder_review_title = '/"oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_title":"(.*?)"/';
                \preg_match($regex_placeholder_review_title, $post_meta, $placeholder_review_title);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_review_title"
                $regex_text_review_description = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_review_description":"(.*?)"/';
                \preg_match($regex_text_review_description, $post_meta, $text_review_description);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_description"
                $regex_placeholder_review_description = '/"oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_description":"(.*?)"/';
                \preg_match($regex_placeholder_review_description, $post_meta, $placeholder_review_description);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_full_name"
                $regex_text_full_name = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_full_name":"(.*?)"/';
                \preg_match($regex_text_full_name, $post_meta, $text_full_name);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_placeholder_full_name"
                $regex_placeholder_full_name = '/"oxy-reviewx-product-tabs_rvx_oxygen_placeholder_full_name":"(.*?)"/';
                \preg_match($regex_placeholder_full_name, $post_meta, $placeholder_full_name);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_email_name"
                $regex_text_email_name = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_email_name":"(.*?)"/';
                \preg_match($regex_text_email_name, $post_meta, $text_email_name);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_placeholder_email_name"
                $regex_placeholder_email_name = '/"oxy-reviewx-product-tabs_rvx_oxygen_placeholder_email_name":"(.*?)"/';
                \preg_match($regex_placeholder_email_name, $post_meta, $placeholder_email_name);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_attachment_title"
                $regex_text_attachment_title = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_attachment_title":"(.*?)"/';
                \preg_match($regex_text_attachment_title, $post_meta, $text_attachment_title);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_placeholder_upload_photo"
                $regex_placeholder_upload_photo = '/"oxy-reviewx-product-tabs_rvx_oxygen_placeholder_upload_photo":"(.*?)"/';
                \preg_match($regex_placeholder_upload_photo, $post_meta, $placeholder_upload_photo);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_mark_as_anonymous"
                $regex_text_mark_as_anonymous = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_mark_as_anonymous":"(.*?)"/';
                \preg_match($regex_text_mark_as_anonymous, $post_meta, $text_mark_as_anonymous);
                // Regular expression "oxy-reviewx-product-tabs_rvx_oxygen_text_recommended_title"
                $regex_text_recommended_title = '/"oxy-reviewx-product-tabs_rvx_oxygen_text_recommended_title":"(.*?)"/';
                \preg_match($regex_text_recommended_title, $post_meta, $text_recommended_title);
                // Output the results with validation
                $write_a_review = !empty($write_a_review) ? esc_html_e($write_a_review[1]) : __('Write a Review', 'reviewx');
                $text_rating_star_title = !empty($text_rating_star_title) ? esc_html_e($text_rating_star_title[1]) : __('Rating', 'reviewx');
                $text_review_title = !empty($text_review_title) ? esc_html_e($text_review_title[1]) : __('Review Title', 'reviewx');
                $placeholder_review_title = !empty($placeholder_review_title) ? esc_html_e($placeholder_review_title[1]) : __('Write Review Title', 'reviewx');
                $text_review_description = !empty($text_review_description) ? esc_html_e($text_review_description[1]) : __('Description', 'reviewx');
                $placeholder_review_description = !empty($placeholder_review_description) ? esc_html_e($placeholder_review_description[1]) : __('Write your description here', 'reviewx');
                $text_full_name = !empty($text_full_name) ? esc_html_e($text_full_name[1]) : __('Full name', 'reviewx');
                $placeholder_full_name = !empty($placeholder_full_name) ? esc_html_e($placeholder_full_name[1]) : __('Full Name', 'reviewx');
                $text_email_name = !empty($text_email_name) ? esc_html_e($text_email_name[1]) : __('Email address', 'reviewx');
                $placeholder_email_name = !empty($placeholder_email_name) ? esc_html_e($placeholder_email_name[1]) : __('Email Address', 'reviewx');
                $text_attachment_title = !empty($text_attachment_title) ? esc_html_e($text_attachment_title[1]) : __('Attachment', 'reviewx');
                $placeholder_upload_photo = !empty($placeholder_upload_photo) ? esc_html_e($placeholder_upload_photo[1]) : __('Upload Photo/Video', 'reviewx');
                $text_mark_as_anonymous = !empty($text_mark_as_anonymous) ? esc_html_e($text_mark_as_anonymous[1]) : __('Mark as Anonymous', 'reviewx');
                $text_recommended_title = !empty($text_recommended_title) ? esc_html_e($text_recommended_title[1]) : __('Recommendation?', ' reviewx');
                // Define the default values, if no builder is active / available then use the default string / texts
                $oxygen_values = ['write_a_review' => $write_a_review, 'text_rating_star_title' => $text_rating_star_title, 'text_review_title' => $text_review_title, 'placeholder_review_title' => $placeholder_review_title, 'text_review_description' => $text_review_description, 'placeholder_review_description' => $placeholder_review_description, 'text_full_name' => $text_full_name, 'placeholder_full_name' => $placeholder_full_name, 'text_email_name' => $text_email_name, 'placeholder_email_name' => $placeholder_email_name, 'text_attachment_title' => $text_attachment_title, 'placeholder_upload_photo' => $placeholder_upload_photo, 'text_mark_as_anonymous' => $text_mark_as_anonymous, 'text_recommended_title' => $text_recommended_title];
                return \json_encode($oxygen_values, \JSON_UNESCAPED_UNICODE);
            }
        }
        return $this->rvxDefaultReviewFormLevelData();
    }
    private function rvxDefaultReviewFormLevelData()
    {
        // Define the default values, if no builder is active / available then use the default string / texts
        $default_values = ['write_a_review' => __('Write a Review', 'reviewx'), 'text_rating_star_title' => __('Rating', 'reviewx'), 'text_review_title' => __('Review Title', 'reviewx'), 'placeholder_review_title' => __('Write Review Title', 'reviewx'), 'text_review_description' => __('Description', 'reviewx'), 'placeholder_review_description' => __('Write your description here', 'reviewx'), 'text_full_name' => __('Full name', 'reviewx'), 'placeholder_full_name' => __('Full Name', 'reviewx'), 'text_email_name' => __('Email address', 'reviewx'), 'placeholder_email_name' => __('Email Address', 'reviewx'), 'text_attachment_title' => __('Attachment', 'reviewx'), 'placeholder_upload_photo' => __('Upload Photo/Video', 'reviewx'), 'text_mark_as_anonymous' => __('Mark as Anonymous', 'reviewx'), 'text_recommended_title' => __('Recommendation?', 'reviewx')];
        return \json_encode($default_values, \JSON_UNESCAPED_UNICODE);
    }
    /*
     * Check is builder active or not
     * Based on that return true or false
     */
    public function builderStatusData()
    {
        // Check builder activation and determine if it's used on the current page
        $builder_status = \false;
        $builder_name = 'none';
        // Check if Elementor is active and used on the current page
        if (did_action('elementor/loaded')) {
            $builder_status = \true;
            $builder_name = 'elementor';
        }
        // Check if Oxygen is active and used on the current page
        if (\function_exists('Rvx\\oxygen_vsb_register_condition')) {
            global $wpdb;
            $page_id = get_the_ID();
            $post_meta_key = 'ct_builder_json';
            // Key used by Oxygen to store builder data
            $oxygen_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $page_id, $post_meta_key));
            if (!empty($oxygen_data)) {
                $builder_status = \true;
                $builder_name = 'oxygen';
            }
        }
        // Check if Divi is active and used on the current page
        if (\function_exists('Rvx\\et_core_is_builder_used_on_current_request')) {
            if (et_core_is_builder_used_on_current_request()) {
                $builder_status = \true;
                $builder_name = 'divi';
            }
        }
        // If no builder is editing the current page, return false
        if (!$builder_status) {
            return [\false, 'none'];
        }
        return [$builder_status, $builder_name];
    }
}
