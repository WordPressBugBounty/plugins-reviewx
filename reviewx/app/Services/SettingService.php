<?php

namespace Rvx\Services;

use Rvx\Api\SettingApi;
class SettingService extends \Rvx\Services\Service
{
    protected SettingApi $settingApi;
    public function __construct()
    {
        $this->settingApi = new SettingApi();
    }
    public function getApiReviewSettings()
    {
        return (new SettingApi())->getApiReviewSettings();
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
    public function getSettingsData() : array
    {
        $rvx_settings = get_option("_rvx_settings_data");
        return $rvx_settings ?? [];
    }
    public function getReviewSettings() : array
    {
        $rvx_settings = get_option("_rvx_settings_data");
        return $rvx_settings['setting']['review_settings'] ?? [];
    }
    public function getWidgetSettings() : array
    {
        $rvx_settings = get_option("_rvx_settings_data");
        return $rvx_settings['setting']['widget_settings'] ?? [];
    }
    /**
     * Upadte Settings Data
     * @return array
     */
    public function updateSettingsData(array $data) : void
    {
        update_option("_rvx_settings_data", $data);
    }
    public function updateReviewSettings(array $review_settings) : void
    {
        $widget_settings = $this->getWidgetSettings();
        $data = $this->updateSettingsMerger($review_settings, $widget_settings);
        update_option("_rvx_settings_data", $data);
    }
    public function updateWidgetSettings(array $widget_settings) : void
    {
        $review_settings = $this->getReviewSettings();
        $data = $this->updateSettingsMerger($review_settings, $widget_settings);
        update_option("_rvx_settings_data", $data);
    }
    private function updateSettingsMerger(array $review_settings, array $widget_settings) : array
    {
        $data = ["setting" => ["widget_settings" => $widget_settings, "review_settings" => $review_settings]];
        return $data ?? [];
    }
    public function wooCommerceVerificationRating()
    {
        if ("no" === get_option('woocommerce_review_rating_verification_label', 'no')) {
            $data = ['active' => \false];
            return $data;
        }
        if ("yes" === get_option('woocommerce_review_rating_verification_label', 'no')) {
            $data = ['active' => \true];
            return $data;
        }
    }
    public function wooVerificationRatingRequired()
    {
        if ("no" === get_option('woocommerce_review_rating_verification_required', 'no')) {
            $data = ['active' => \false];
            return $data;
        }
        if ("yes" === get_option('woocommerce_review_rating_verification_required', 'no')) {
            $data = ['active' => \true];
            return $data;
        }
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
        return $this->settingApi->userCurrentPlan();
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
    }
    public function removeCredentials()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        $sql = "TRUNCATE TABLE {$table_name}";
        $wpdb->query($sql);
        if ($wpdb->last_error) {
            return ['message' => "Table truncate successfully"];
        }
    }
    public function getLocalSettings()
    {
        return (new SettingApi())->getLocalSettings();
    }
}
