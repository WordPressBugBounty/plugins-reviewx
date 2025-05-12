<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\Services\SettingService;
class MigrationPrompt
{
    public function rvx_retrieve_old_plugin_options_data()
    {
        $data = [];
        $sharedMethods = new \Rvx\Handlers\MigrationRollback\SharedMethods();
        // Options to retrieve
        if ($sharedMethods->key_exists('_rx_option_review_criteria')) {
            $data['multicriteria'] = $sharedMethods->rvxOldReviewCriteriaConverter();
        }
        if ($sharedMethods->key_exists('_rx_option_allow_like_dislike')) {
            $data['enable_likes_dislikes']['enabled'] = get_option('_rx_option_allow_like_dislike');
            $data['enable_likes_dislikes']['options']['allow_dislikes'] = get_option('_rx_option_allow_like_dislike');
        }
        if ($sharedMethods->key_exists('_rx_option_color_theme')) {
            $data['brand_color_code'] = get_option('_rx_option_color_theme');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_img')) {
            $data['photo_reviews_allowed'] = get_option('_rx_option_allow_img');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_share_review')) {
            $data['allow_review_sharing'] = get_option('_rx_option_allow_share_review');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_auto_approval')) {
            $data['auto_approve_reviews'] = get_option('_rx_option_disable_auto_approval');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_review_title')) {
            $data['allow_review_titles'] = get_option('_rx_option_allow_review_title');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_reviewer_name_censor')) {
            $data['censor_reviewer_name'] = get_option('_rx_option_allow_reviewer_name_censor');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_richschema')) {
            $data['product_schema'] = get_option('_rx_option_disable_richschema');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_video')) {
            $data['video_reviews_allowed'] = get_option('_rx_option_allow_video');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_multiple_review')) {
            $data['allow_multiple_reviews'] = get_option('_rx_option_allow_multiple_review');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_anonymouse')) {
            $data['anonymous_reviews_allowed'] = get_option('_rx_option_allow_anonymouse');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_recaptcha')) {
            $data['recaptcha']['enabled'] = get_option('_rx_option_allow_recaptcha');
        }
        if ($sharedMethods->key_exists('_rx_option_recaptcha_site_key')) {
            $data['recaptcha']['site_key'] = get_option('_rx_option_recaptcha_site_key');
        }
        if ($sharedMethods->key_exists('_rx_option_recaptcha_secret_key')) {
            $data['recaptcha']['secret_key'] = get_option('_rx_option_recaptcha_secret_key');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_richschema')) {
            $data['product_schema'] = get_option('_rx_option_disable_richschema');
        }
        if ($sharedMethods->key_exists('_rx_option_review_per_page')) {
            $data['per_page_reviews'] = get_option('_rx_option_review_per_page');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_recommendation')) {
            $data['allow_recommendations'] = get_option('_rx_option_allow_recommendation');
        }
        if ($sharedMethods->key_exists('_rx_option_filter_recent')) {
            $data['filter_and_sort_options']['recent'] = get_option('_rx_option_filter_recent');
        }
        if ($sharedMethods->key_exists('_rx_option_filter_photo')) {
            $data['filter_and_sort_options']['photo'] = get_option('_rx_option_filter_photo');
        }
        if (empty($data)) {
            return \false;
        }
        return $data;
    }
    public function rvx_retrieve_saas_plugin_options_data()
    {
        $settings = (array) (new SettingService())->getSettingsData()['setting'] ?? [];
        $saasOptions = ['display_badges' => $settings['widget_settings']['display_badges'], 'outline' => $settings['widget_settings']['outline'], 'brand_color_code' => $settings['widget_settings']['brand_color_code'], 'star_color_code' => $settings['widget_settings']['star_color_code'], 'button_font_color_code' => $settings['widget_settings']['button_font_color_code'], 'filter_and_sort_options' => $settings['widget_settings']['filter_and_sort_options'], 'verified_customer_only' => $settings['review_settings']['reviews']['review_submission_policy']['options']['verified_customer'], 'review_eligibility' => $settings['review_settings']['reviews']['review_eligibility'], 'auto_approve_reviews' => $settings['review_settings']['reviews']['auto_approve_reviews'], 'show_reviewer_name' => $settings['review_settings']['reviews']['show_reviewer_name'], 'censor_reviewer_name' => $settings['review_settings']['reviews']['censor_reviewer_name'], 'show_reviewer_country' => $settings['review_settings']['reviews']['show_reviewer_country'], 'enable_likes_dislikes' => $settings['review_settings']['reviews']['enable_likes_dislikes'], 'allow_review_sharing' => $settings['review_settings']['reviews']['allow_review_sharing'], 'allow_review_titles' => $settings['review_settings']['reviews']['allow_review_titles'], 'photo_reviews_allowed' => $settings['review_settings']['reviews']['photo_reviews_allowed'], 'video_reviews_allowed' => $settings['review_settings']['reviews']['video_reviews_allowed'], 'allow_recommendations' => $settings['review_settings']['reviews']['allow_recommendations'], 'anonymous_reviews_allowed' => $settings['review_settings']['reviews']['anonymous_reviews_allowed'], 'multicriteria' => $settings['review_settings']['reviews']['multicriteria'], 'product_schema' => $settings['review_settings']['reviews']['product_schema'], 'recaptcha' => $settings['review_settings']['reviews']['recaptcha']];
        return $saasOptions;
    }
}
