<?php

namespace Rvx\Services;

use Rvx\Api\SettingApi;
class SettingService extends \Rvx\Services\Service
{
    protected $settingApi;
    public function __construct()
    {
        // $this->settingApi = new SettingApi();
    }
    public function getApiReviewSettings($data)
    {
        return (new SettingApi())->getApiReviewSettings($data);
    }
    public function saveApiReviewSettings($data)
    {
        return (new SettingApi())->saveApiReviewSettings($data);
    }
    public function getApiWidgetSettings()
    {
        return (new SettingApi())->getAPiWidgetSettings();
    }
    public function saveWidgetSettings($data)
    {
        return (new SettingApi())->saveApiWidgetSettings($data);
    }
    /**
     * Get Settings Data
     * @return array
     */
    public function getSettingsData($post_type = null) : array
    {
        $review_settings = $this->getReviewSettings($post_type);
        $widget_settings = $this->getWidgetSettings();
        $rvx_settings = $this->formatSettings($review_settings, $widget_settings);
        // Ensure we always return an array even if invalid data exists
        return \is_array($rvx_settings) ? $rvx_settings : [];
    }
    public function getReviewSettings($post_type = null) : array
    {
        $default_cpt_name = 'product';
        if ($post_type !== null) {
            $default_cpt_name = $post_type;
        }
        $option_name = '_rvx_settings_' . $default_cpt_name;
        $rvx_settings = get_option($option_name, \false);
        if ($post_type === 'product' && $rvx_settings === \false) {
            $rvx_settings = get_option('_rvx_settings_data');
        }
        return $rvx_settings['setting']['review_settings'] ?? [];
    }
    public function getWidgetSettings() : array
    {
        $option_name = '_rvx_settings_widget';
        $rvx_settings = get_option($option_name, \false);
        if ($rvx_settings === \false) {
            $rvx_settings = get_option('_rvx_settings_data');
        }
        return $rvx_settings['setting']['widget_settings'] ?? [];
    }
    /**
     * Upadte Settings Data
     * @return array
     */
    public function updateSettingsData(array $data, $post_type = null) : void
    {
        update_option("_rvx_settings_data", $data);
    }
    public function updateReviewSettings(array $review_settings, $post_type = null) : void
    {
        $default_cpt_name = 'product';
        if ($post_type !== null) {
            $default_cpt_name = $post_type;
            if ($post_type === 'product') {
                $review_settings = $review_settings['reviews'];
            }
        }
        $option_name = '_rvx_settings_' . $default_cpt_name;
        $data = ["setting" => ["review_settings" => ["reviews" => $review_settings]]];
        if ($post_type !== 'product') {
            // Define the review submission policy
            $policy = ["review_submission_policy" => ["options" => ["anyone" => 1]]];
            // Ensure reviews is an array and merge policy directly into it
            if (!\is_array($data['setting']['review_settings']['reviews'])) {
                $data['setting']['review_settings']['reviews'] = [];
            }
            // Merge the policy directly at the top level of "reviews"
            $data['setting']['review_settings']['reviews'] = \array_merge($policy, $data['setting']['review_settings']['reviews']);
        }
        update_option($option_name, $data);
    }
    public function updateReviewSettingsOnSync(array $review_settings, $post_type = null) : void
    {
        $default_cpt_name = 'product';
        if ($post_type !== null) {
            $default_cpt_name = $post_type;
            $review_settings = $review_settings['reviews'];
        }
        $option_name = '_rvx_settings_' . $default_cpt_name;
        $data = ["setting" => ["review_settings" => ["reviews" => $review_settings]]];
        if ($post_type !== 'product') {
            // Define the review submission policy
            $policy = ["review_submission_policy" => ["options" => ["anyone" => 1]]];
            // Ensure reviews is an array and merge policy directly into it
            if (!\is_array($data['setting']['review_settings']['reviews'])) {
                $data['setting']['review_settings']['reviews'] = [];
            }
            // Merge the policy directly at the top level of "reviews"
            $data['setting']['review_settings']['reviews'] = \array_merge($policy, $data['setting']['review_settings']['reviews']);
        }
        update_option($option_name, $data);
    }
    public function updateWidgetSettings(array $widget_settings) : void
    {
        $data = ["setting" => ["widget_settings" => $widget_settings]];
        update_option("_rvx_settings_widget", $data);
    }
    private function formatSettings(array $review_settings, array $widget_settings) : array
    {
        $data = ["setting" => ["review_settings" => $review_settings, "widget_settings" => $widget_settings]];
        return $data ?? [];
    }
    public function wooCommerceVerificationRating() : array
    {
        $value = get_option('woocommerce_review_rating_verification_label', 'no');
        return ['active' => $value === 'yes'];
    }
    public function wooVerificationRatingRequired() : array
    {
        $value = get_option('woocommerce_review_rating_verification_required', 'no');
        return ['active' => $value === 'yes'];
    }
    public function wooCommerceVerificationRatingUpdate($data)
    {
        if ($data['active'] == \true) {
            update_option('woocommerce_review_rating_verification_label', 'yes');
            $data = ['success' => \true, 'message' => __("Verified Owner Active")];
            return $data;
        }
        if ($data['active'] == \false) {
            update_option('woocommerce_review_rating_verification_label', 'no');
            $data = ['success' => \true, 'message' => __("Verified Owner Deactive")];
            return $data;
        }
    }
    public function wooVerificationRating($data)
    {
        if ($data['active'] == \true) {
            update_option('woocommerce_review_rating_verification_required', 'yes');
            $data = ['success' => \true, 'message' => __("Reviews can only be left by verified owners active")];
            return $data;
        }
        if ($data['active'] == \false) {
            update_option('woocommerce_review_rating_verification_required', 'no');
            $data = ['success' => \true, 'message' => __("Reviews can only be left by verified owners deactive")];
            return $data;
        }
    }
    public function userCurrentPlan()
    {
        return (new SettingApi())->userCurrentPlan();
    }
    public function getApiGeneralSettings()
    {
        return (new SettingApi())->getApiGeneralSettings();
    }
    public function saveApiGeneralSettings($data)
    {
        return (new SettingApi())->saveApiGeneralSettings($data);
    }
    public function allSettingsSave($data)
    {
        $payload_json = \json_encode($data['settings']);
        update_option('rvx_all_setting_data', $payload_json);
        return ['message' => __('Settings saved successfully'), 'data' => $data['settings']];
    }
    public function removeCredentials()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        $sql = "TRUNCATE TABLE {$table_name}";
        $result = $wpdb->query($sql);
        if ($wpdb->last_error) {
            return ['message' => 'Error: ' . $wpdb->last_error];
        }
        return ['message' => 'Table truncated successfully', 'result' => $result];
    }
    public function getLocalSettings($post_type)
    {
        return (new SettingApi())->getLocalSettings($post_type);
    }
}
