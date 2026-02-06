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
    public function removeCredentials($requestData)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        $sql = "TRUNCATE TABLE {$table_name}";
        $result = $wpdb->query($sql);
        if ($wpdb->last_error) {
            return ['message' => 'Error: ' . $wpdb->last_error];
        }
        return ['message' => 'Site Table deleted successfully', 'result' => $result];
    }
    public function updateSiteData($requestHeaders)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        // --- Extract headers (headers come as arrays) ---
        $user_email = isset($requestHeaders['x_user_email'][0]) ? sanitize_email($requestHeaders['x_user_email'][0]) : '';
        $user_name = isset($requestHeaders['x_user_name'][0]) ? sanitize_text_field($requestHeaders['x_user_name'][0]) : '';
        $site_uid = isset($requestHeaders['x_site_uid'][0]) ? sanitize_text_field($requestHeaders['x_site_uid'][0]) : '';
        // $site_id    = isset($requestHeaders['x_site_id'][0])    ? intval($requestHeaders['x_site_id'][0])                 : 0;
        // $domain     = isset($requestHeaders['x_domain'][0])     ? sanitize_text_field($requestHeaders['x_domain'][0])     : '';
        // --- Validation: must provide at least one field to update ---
        if (empty($user_email) && empty($user_name)) {
            // error_log('[ReviewX] updateSiteData: Missing X-User-Email and X-User-Name in request headers.');
            return ['status' => 'fail', 'message' => 'Missing X-User-Email and X-User-Name in request headers.'];
        }
        // --- Decide which identifier to use for WHERE (preferred order: uid, site_id, domain) ---
        $where = [];
        $where_fmt = [];
        if (!empty($site_uid)) {
            $where = ['uid' => $site_uid];
            $where_fmt = ['%s'];
        } else {
            // No identifier provided — refuse to do a global update for safety
            // error_log('[ReviewX] updateSiteData: No site identifier provided (x_site_uid, x_site_id or x_domain). Aborting to avoid global update.');
            return ['status' => 'fail', 'message' => 'Missing site identifier. Provide x_site_uid, x_site_id or x_domain in request headers.'];
        }
        // --- Prepare data to update ---
        $data = [];
        $format = [];
        if (!empty($user_email)) {
            $data['email'] = $user_email;
            $format[] = '%s';
        }
        if (!empty($user_name)) {
            $data['name'] = $user_name;
            $format[] = '%s';
        }
        if (empty($data)) {
            return ['status' => 'fail', 'message' => 'No valid update fields provided.'];
        }
        // error_log('[ReviewX] updateSiteData: Target WHERE: ' . print_r($where, true) . ' — Updating: ' . print_r($data, true));
        // --- Fetch existing row to detect no-op updates and to ensure the row exists ---
        $where_keys = \array_keys($where);
        // Build a safe WHERE clause and prepare values
        $where_clauses = [];
        $where_values = [];
        foreach ($where as $col => $val) {
            $where_clauses[] = "{$col} = %s";
            $where_values[] = (string) $val;
        }
        $where_sql = \implode(' AND ', $where_clauses);
        $select_sql = $wpdb->prepare("SELECT * FROM {$table_name} WHERE {$where_sql} LIMIT 1", $where_values);
        $existing = $wpdb->get_row($select_sql, ARRAY_A);
        if (null === $existing) {
            // error_log('[ReviewX] updateSiteData: No site row found for identifier: ' . print_r($where, true));
            return ['status' => 'fail', 'message' => 'No site found matching provided identifier.', 'where' => $where];
        }
        // Compare values — if identical, return success (no change needed)
        $is_same = \true;
        foreach ($data as $col => $val) {
            $existing_val = isset($existing[$col]) ? (string) $existing[$col] : '';
            if ($existing_val !== (string) $val) {
                $is_same = \false;
                break;
            }
        }
        if ($is_same) {
            return ['status' => 'success', 'message' => 'No changes required — data already up to date.', 'data' => $data, 'where' => $where];
        }
        // --- Perform the update using $wpdb->update (safe) ---
        $updated = $wpdb->update($table_name, $data, $where, $format, $where_fmt);
        if ($wpdb->last_error) {
            // error_log('[ReviewX] updateSiteData: DB error - ' . $wpdb->last_error);
            return ['status' => 'error', 'message' => 'Database error: ' . $wpdb->last_error];
        }
        // $updated can be: false (error), 0 (no rows changed), >0 (rows updated)
        if ($updated === \false) {
            return ['status' => 'error', 'message' => 'Failed to update site data.'];
        }
        $rows = $wpdb->rows_affected;
        return ['status' => 'success', 'message' => $rows > 0 ? "Site data updated successfully ({$rows} row(s) affected)." : 'No rows were changed.', 'data' => $data, 'where' => $where];
    }
    public function getLocalSettings($post_type)
    {
        return (new SettingApi())->getLocalSettings($post_type);
    }
}
