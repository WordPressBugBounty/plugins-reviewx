<?php

namespace ReviewX\Handlers\MigrationRollback;

use ReviewX\Services\SettingService;
class MigrationPrompt
{
    public function reviewx_retrieve_old_plugin_options_data()
    {
        $data = [];
        $sharedMethods = new \ReviewX\Handlers\MigrationRollback\SharedMethods();
        // Options to retrieve
        if ($sharedMethods->key_exists('_rx_option_review_criteria')) {
            $data['multicriteria'] = $sharedMethods->reviewx_old_review_criteria_converter();
        }
        if ($sharedMethods->key_exists('_rx_option_allow_like_dislike')) {
            $data['enable_likes_dislikes']['enabled'] = \get_option('_rx_option_allow_like_dislike');
            $data['enable_likes_dislikes']['options']['allow_dislikes'] = \get_option('_rx_option_allow_like_dislike');
        }
        if ($sharedMethods->key_exists('_rx_option_color_theme')) {
            $data['brand_color_code'] = \get_option('_rx_option_color_theme');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_img')) {
            $data['photo_reviews_allowed'] = \get_option('_rx_option_allow_img');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_share_review')) {
            $data['allow_review_sharing'] = \get_option('_rx_option_allow_share_review');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_auto_approval')) {
            $data['auto_approve_reviews'] = \get_option('_rx_option_disable_auto_approval');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_review_title')) {
            $data['allow_review_titles'] = \get_option('_rx_option_allow_review_title');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_reviewer_name_censor')) {
            $data['censor_reviewer_name'] = \get_option('_rx_option_allow_reviewer_name_censor');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_richschema')) {
            $data['product_schema'] = \get_option('_rx_option_disable_richschema');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_video')) {
            $data['video_reviews_allowed'] = \get_option('_rx_option_allow_video');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_multiple_review')) {
            $data['allow_multiple_reviews'] = \get_option('_rx_option_allow_multiple_review');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_anonymouse')) {
            $data['anonymous_reviews_allowed'] = \get_option('_rx_option_allow_anonymouse');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_recaptcha')) {
            $data['recaptcha']['enabled'] = \get_option('_rx_option_allow_recaptcha');
        }
        if ($sharedMethods->key_exists('_rx_option_recaptcha_site_key')) {
            $data['recaptcha']['site_key'] = \get_option('_rx_option_recaptcha_site_key');
        }
        if ($sharedMethods->key_exists('_rx_option_recaptcha_secret_key')) {
            $data['recaptcha']['secret_key'] = \get_option('_rx_option_recaptcha_secret_key');
        }
        if ($sharedMethods->key_exists('_rx_option_disable_richschema')) {
            $data['product_schema'] = \get_option('_rx_option_disable_richschema');
        }
        if ($sharedMethods->key_exists('_rx_option_review_per_page')) {
            $data['per_page_reviews'] = \get_option('_rx_option_review_per_page');
        }
        if ($sharedMethods->key_exists('_rx_option_allow_recommendation')) {
            $data['allow_recommendations'] = \get_option('_rx_option_allow_recommendation');
        }
        if ($sharedMethods->key_exists('_rx_option_filter_recent')) {
            $data['filter_and_sort_options']['recent'] = \get_option('_rx_option_filter_recent');
        }
        if ($sharedMethods->key_exists('_rx_option_filter_photo')) {
            $data['filter_and_sort_options']['photo'] = \get_option('_rx_option_filter_photo');
        }
        if (empty($data)) {
            return \false;
        }
        return $data;
    }
    public function reviewx_retrieve_saas_plugin_options_data()
    {
        $settings_data = (new SettingService())->getSettingsData();
        $settings = isset($settings_data['setting']) && \is_array($settings_data['setting']) ? $settings_data['setting'] : [];
        $widget_settings = isset($settings['widget_settings']) && \is_array($settings['widget_settings']) ? $settings['widget_settings'] : [];
        $review_settings = isset($settings['review_settings']['reviews']) && \is_array($settings['review_settings']['reviews']) ? $settings['review_settings']['reviews'] : [];
        $saasOptions = [];
        $widget_option_keys = ['display_badges', 'outline', 'brand_color_code', 'star_color_code', 'button_font_color_code', 'filter_and_sort_options'];
        foreach ($widget_option_keys as $key) {
            if (\array_key_exists($key, $widget_settings)) {
                $saasOptions[$key] = $widget_settings[$key];
            }
        }
        $review_option_paths = ['verified_customer_only' => ['review_submission_policy', 'options', 'verified_customer'], 'review_eligibility' => ['review_eligibility'], 'auto_approve_reviews' => ['auto_approve_reviews'], 'show_reviewer_name' => ['show_reviewer_name'], 'censor_reviewer_name' => ['censor_reviewer_name'], 'show_reviewer_country' => ['show_reviewer_country'], 'enable_likes_dislikes' => ['enable_likes_dislikes'], 'allow_review_sharing' => ['allow_review_sharing'], 'allow_review_titles' => ['allow_review_titles'], 'photo_reviews_allowed' => ['photo_reviews_allowed'], 'video_reviews_allowed' => ['video_reviews_allowed'], 'allow_recommendations' => ['allow_recommendations'], 'anonymous_reviews_allowed' => ['anonymous_reviews_allowed'], 'multicriteria' => ['multicriteria'], 'product_schema' => ['product_schema'], 'recaptcha' => ['recaptcha']];
        foreach ($review_option_paths as $key => $path) {
            $found = \false;
            $value = $this->get_array_value($review_settings, $path, $found);
            if ($found) {
                $saasOptions[$key] = $value;
            }
        }
        return $saasOptions;
    }
    private function get_array_value(array $source, array $path, &$found = \false)
    {
        $value = $source;
        foreach ($path as $segment) {
            if (!\is_array($value) || !\array_key_exists($segment, $value)) {
                $found = \false;
                return null;
            }
            $value = $value[$segment];
        }
        $found = \true;
        return $value;
    }
}
