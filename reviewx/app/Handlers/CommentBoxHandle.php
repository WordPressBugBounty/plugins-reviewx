<?php

namespace Rvx\Handlers;

use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Facades\Config;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\DB;
use Rvx\WPDrill\Facades\View;
use Rvx\Utilities\Helper;
class CommentBoxHandle
{
    public function __invoke() : void
    {
        if (\class_exists('WooCommerce') && !is_account_page() && Client::getSync()) {
            $attributes = $this->setRvxAttributes();
            $formData = $this->builderCustomizedFormTextsData();
            $this->commentBoxDefaultStyleForCustomPostType();
            View::output('storefront/widget', ['data' => $attributes, 'formLevelData' => $formData]);
        }
    }
    public function setRvxAttributes()
    {
        $user_id = get_current_user_id();
        $user_name = Helper::loggedInUserFullName() ?: Helper::loggedInUserDisplayName();
        $attributes = ['product' => ['id' => get_the_ID()], 'userInfo' => ['isLoggedIn' => Helper::loggedIn(), 'id' => Helper::userId(), 'name' => $user_name, 'email' => Helper::loggedInUserEmail(), 'isVerified' => Helper::verifiedCustomer($user_id)]];
        return \json_encode($attributes);
        /** 
        if(is_admin()){
            echo '<script>
                window.parent.__rvx_attributes__ = {
                    ...window.parent.__rvx_attributes__,
                    singleProduct:' . $attributesJson . '
                }
            </script>';
        } else{
            echo '<script>
                window.__rvx_attributes__ = {
                    ...window.__rvx_attributes__,
                    singleProduct:' . $attributesJson . '
                }
            </script>';
        }
        */
    }
    public function commentBoxDefaultStyleForCustomPostType() : void
    {
        $data = get_option('_rvx_custom_post_type_settings');
        $enabled_post_types = [];
        if ($data && Helper::arrayGet($data, 'code') !== 40000) {
            foreach ($data['data']['reviews'] as $review) {
                if ($review['status'] === 'Enabled') {
                    $enabled_post_types[] = $review['post_type'];
                }
            }
        }
        $enabled_post_types[] = 'product';
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
        if ($builder_status_data[0] === \true) {
            if ($builder_status_data[1] === 'elementor') {
                // Elementor builder is active
                global $builderElementorSetting;
                return \json_encode($builderElementorSetting);
            } elseif ($builder_status_data[1] === 'oxygen') {
                // Oxygen builder is active
                // SQL query to fetch the post_id based on meta_key and meta_value criteria
                global $wpdb;
                $query = "\n                    SELECT post_id \n                    FROM {$wpdb->prefix}postmeta \n                    WHERE meta_key = '_ct_builder_json' \n                    AND meta_value LIKE '%oxy-reviewx-product-tabs_rvx_oxygen%' \n                    ORDER BY post_id DESC \n                    LIMIT 1\n                ";
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
                        $write_a_review = !empty($write_a_review) ? esc_html($write_a_review[1]) : 'Write a Review';
                        $text_rating_star_title = !empty($text_rating_star_title) ? esc_html($text_rating_star_title[1]) : 'Rating';
                        $text_review_title = !empty($text_review_title) ? esc_html($text_review_title[1]) : 'Review Title';
                        $placeholder_review_title = !empty($placeholder_review_title) ? esc_html($placeholder_review_title[1]) : 'Write Review Title';
                        $text_review_description = !empty($text_review_description) ? esc_html($text_review_description[1]) : 'Description';
                        $placeholder_review_description = !empty($placeholder_review_description) ? esc_html($placeholder_review_description[1]) : 'Write your description here';
                        $text_full_name = !empty($text_full_name) ? esc_html($text_full_name[1]) : 'Full name';
                        $placeholder_full_name = !empty($placeholder_full_name) ? esc_html($placeholder_full_name[1]) : 'Full Name';
                        $text_email_name = !empty($text_email_name) ? esc_html($text_email_name[1]) : 'Email address';
                        $placeholder_email_name = !empty($placeholder_email_name) ? esc_html($placeholder_email_name[1]) : 'Email Address';
                        $text_attachment_title = !empty($text_attachment_title) ? esc_html($text_attachment_title[1]) : 'Attachment';
                        $placeholder_upload_photo = !empty($placeholder_upload_photo) ? esc_html($placeholder_upload_photo[1]) : 'Upload Photo/Video';
                        $text_mark_as_anonymous = !empty($text_mark_as_anonymous) ? esc_html($text_mark_as_anonymous[1]) : 'Mark as Anonymous';
                        $text_recommended_title = !empty($text_recommended_title) ? esc_html($text_recommended_title[1]) : 'Recommendation?';
                        // Define the default values, if no builder is active / available then use the default string / texts
                        $oxygen_values = ['write_a_review' => $write_a_review, 'text_rating_star_title' => $text_rating_star_title, 'text_review_title' => $text_review_title, 'placeholder_review_title' => $placeholder_review_title, 'text_review_description' => $text_review_description, 'placeholder_review_description' => $placeholder_review_description, 'text_full_name' => $text_full_name, 'placeholder_full_name' => $placeholder_full_name, 'text_email_name' => $text_email_name, 'placeholder_email_name' => $placeholder_email_name, 'text_attachment_title' => $text_attachment_title, 'placeholder_upload_photo' => $placeholder_upload_photo, 'text_mark_as_anonymous' => $text_mark_as_anonymous, 'text_recommended_title' => $text_recommended_title];
                        return \json_encode($oxygen_values);
                    } else {
                        return $this->rvxDefaultReviewFormLevelData();
                    }
                } else {
                    return $this->rvxDefaultReviewFormLevelData();
                }
            } elseif ($builder_status_data[1] === 'divi') {
                // Divi builder is active
            }
        } else {
            return $this->rvxDefaultReviewFormLevelData();
        }
        return $this->rvxDefaultReviewFormLevelData();
    }
    public function rvxDefaultReviewFormLevelData()
    {
        // Define the default values, if no builder is active / available then use the default string / texts
        $default_values = ['write_a_review' => 'Write a Review', 'text_rating_star_title' => 'Rating', 'text_review_title' => 'Review Title', 'placeholder_review_title' => 'Write Review Title', 'text_review_description' => 'Description', 'placeholder_review_description' => 'Write your description here', 'text_full_name' => 'Full name', 'placeholder_full_name' => 'Full Name', 'text_email_name' => 'Email address', 'placeholder_email_name' => 'Email Address', 'text_attachment_title' => 'Attachment', 'placeholder_upload_photo' => 'Upload Photo/Video', 'text_mark_as_anonymous' => 'Mark as Anonymous', 'text_recommended_title' => 'Recommendation?'];
        return \json_encode($default_values);
    }
    /*
     * Check is builder active or not
     * Based on that return true or false
     */
    public function builderStatusData()
    {
        // Check builder activation on the page
        $builder_status = \false;
        $builder_name = 'none';
        // Check Builder activation on the page
        if (did_action('elementor/loaded')) {
            $builder_status = \true;
            $builder_name = 'elementor';
        }
        if (\function_exists('Rvx\\oxygen_vsb_register_condition')) {
            $builder_status = \true;
            $builder_name = 'oxygen';
        }
        if (\function_exists('Rvx\\et_core_is_builder_used_on_current_request')) {
            $builder_status = \true;
            $builder_name = 'divi';
        }
        /*
        $builder_status_data = [
            'status' => $builder_status,
            'name' => $builder_name,
        ];
        */
        return [$builder_status, $builder_name];
    }
}
