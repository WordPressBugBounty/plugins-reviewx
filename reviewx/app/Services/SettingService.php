<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\SettingApi;
class SettingService extends \Rvx\Services\Service
{
    protected SettingApi $settingApi;
    public function __construct()
    {
        $this->settingApi = new SettingApi();
    }
    public function getReviewSettings()
    {
        return (new SettingApi())->getReviewSettings();
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
    public function reviewxSetting()
    {
    }
    public function saveReviewSettings($data)
    {
        return (new SettingApi())->saveReviewSettings($data);
    }
    public function getWidgetSettings()
    {
        return (new SettingApi())->getWidgetSettings();
    }
    public function userCurrentPlan()
    {
        return $this->settingApi->userCurrentPlan();
    }
    public function saveWidgetSettings($data)
    {
        return (new SettingApi())->saveWidgetSettings($data);
    }
    public function getGeneralSettings()
    {
        return (new SettingApi())->getGeneralSettings();
    }
    public function saveGeneralSettings($data)
    {
        return (new SettingApi())->saveGeneralSettings($data);
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
