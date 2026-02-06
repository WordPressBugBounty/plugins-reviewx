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
            default:
                return $this->rvxDefaultReviewFormLevelData();
        }
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
