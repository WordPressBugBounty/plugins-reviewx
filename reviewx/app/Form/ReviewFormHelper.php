<?php

namespace Rvx\Form;

use Rvx\CPT\CptHelper;
class ReviewFormHelper
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function builderCustomizedFormTextsData()
    {
        // Helper function to extract specific Builder data
        $builder_status_data = $this->builderStatusData();
        switch ($builder_status_data['builder_name']) {
            case 'elementor':
                global $builderElementorSetting;
                return !empty($builderElementorSetting) ? \json_encode($builderElementorSetting, \JSON_UNESCAPED_UNICODE) : $this->rvxDefaultReviewFormLevelData();
            case 'oxygen':
                return $this->builderOxygenReviewFormLevelData();
            case 'divi':
                return $this->rvxDefaultReviewFormLevelData();
            default:
                return $this->rvxDefaultReviewFormLevelData();
        }
    }
    private function builderOxygenReviewFormLevelData()
    {
        global $wpdb;
        $query = "\n            SELECT post_id \n            FROM {$wpdb->prefix}postmeta \n            WHERE meta_key = '_ct_builder_json' \n            AND meta_value LIKE '%oxy-reviewx-product-tabs_rvx_oxygen%' \n            ORDER BY post_id DESC \n            LIMIT 1\n        ";
        $post_id = $wpdb->get_var($query);
        $post_meta = get_post_meta($post_id, '_ct_builder_json', \true);
        // Use OR here
        if (!$post_id || empty($post_meta)) {
            return $this->rvxDefaultReviewFormLevelData();
        }
        $data = \json_decode($post_meta, \true);
        $source = $data['options'] ?? $data;
        // fallback if structure differs
        $oxygen_values = ['write_a_review' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_write_a_review'] ?? __('Write a Review', 'reviewx'), 'text_rating_star_title' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_rating_star_title'] ?? __('Rating', 'reviewx'), 'text_review_title' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_review_title'] ?? __('Review Title', 'reviewx'), 'placeholder_review_title' => $source['oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_title'] ?? __('Write Review Title', 'reviewx'), 'text_review_description' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_review_description'] ?? __('Description', 'reviewx'), 'placeholder_review_description' => $source['oxy-reviewx-product-tabs_rvx_oxygen_placeholder_review_description'] ?? __('Write your description here', 'reviewx'), 'text_full_name' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_full_name'] ?? __('Full name', 'reviewx'), 'placeholder_full_name' => $source['oxy-reviewx-product-tabs_rvx_oxygen_placeholder_full_name'] ?? __('Full Name', 'reviewx'), 'text_email_name' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_email_name'] ?? __('Email address', 'reviewx'), 'placeholder_email_name' => $source['oxy-reviewx-product-tabs_rvx_oxygen_placeholder_email_name'] ?? __('Email Address', 'reviewx'), 'text_attachment_title' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_attachment_title'] ?? __('Attachment', 'reviewx'), 'placeholder_upload_photo' => $source['oxy-reviewx-product-tabs_rvx_oxygen_placeholder_upload_photo'] ?? __('Upload Photo / Video', 'reviewx'), 'text_mark_as_anonymous' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_mark_as_anonymous'] ?? __('Mark as Anonymous', 'reviewx'), 'text_recommended_title' => $source['oxy-reviewx-product-tabs_rvx_oxygen_text_recommended_title'] ?? __('Recommendation?', 'reviewx')];
        return \json_encode($oxygen_values, \JSON_UNESCAPED_UNICODE);
    }
    private function rvxDefaultReviewFormLevelData()
    {
        // Define the default values, if no builder is active / available then use the default string / texts
        $default_values = ['write_a_review' => __('Write a Review', 'reviewx'), 'text_rating_star_title' => __('Rating', 'reviewx'), 'text_review_title' => __('Review Title', 'reviewx'), 'placeholder_review_title' => __('Write Review Title', 'reviewx'), 'text_review_description' => __('Description', 'reviewx'), 'placeholder_review_description' => __('Write your description here', 'reviewx'), 'text_full_name' => __('Full name', 'reviewx'), 'placeholder_full_name' => __('Full Name', 'reviewx'), 'text_email_name' => __('Email address', 'reviewx'), 'placeholder_email_name' => __('Email Address', 'reviewx'), 'text_attachment_title' => __('Attachment', 'reviewx'), 'placeholder_upload_photo' => __('Upload Photo / Video', 'reviewx'), 'text_mark_as_anonymous' => __('Mark as Anonymous', 'reviewx'), 'text_recommended_title' => __('Recommendation?', 'reviewx')];
        return \json_encode($default_values, \JSON_UNESCAPED_UNICODE);
    }
    /*
     * Check is builder active or not
     * Based on that return true or false
     */
    public function builderStatusData() : array
    {
        $builder_status = \false;
        $builder_name = 'none';
        // Elementor
        if (did_action('elementor/loaded')) {
            $builder_status = \true;
            $builder_name = 'elementor';
        }
        // Oxygen
        if (\function_exists('Rvx\\oxygen_vsb_register_condition')) {
            global $wpdb;
            $page_id = get_the_ID();
            $post_meta_key = '_ct_builder_json';
            // Oxygen stores builder data here
            $oxygen_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $page_id, $post_meta_key));
            if (!empty($oxygen_data)) {
                $builder_status = \true;
                $builder_name = 'oxygen';
            }
        }
        // Divi
        if (\function_exists('Rvx\\et_core_is_builder_used_on_current_request') && et_core_is_builder_used_on_current_request()) {
            $builder_status = \true;
            $builder_name = 'divi';
        }
        return ['builder_status' => $builder_status, 'builder_name' => $builder_name];
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
    public function rvxEnabledPostTypes() : array
    {
        return $this->cptHelper->enabledCPT();
    }
}
