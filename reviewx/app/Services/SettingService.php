<?php

namespace ReviewX\Services;

\defined("ABSPATH") || exit;
use ReviewX\Api\SettingApi;
class SettingService extends \ReviewX\Services\Service
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
        $reviewx_settings = $this->formatSettings($review_settings, $widget_settings);
        // Ensure we always return an array even if invalid data exists
        return \is_array($reviewx_settings) ? $reviewx_settings : [];
    }
    public function getReviewSettings($post_type = null) : array
    {
        $default_cpt_name = 'product';
        if ($post_type !== null) {
            $default_cpt_name = $post_type;
        }
        $option_name = '_rvx_settings_' . $default_cpt_name;
        $reviewx_settings = \get_option($option_name, \false);
        if ($post_type === 'product' && $reviewx_settings === \false) {
            $reviewx_settings = \get_option('_rvx_settings_data');
        }
        return $reviewx_settings['setting']['review_settings'] ?? [];
    }
    public function getWidgetSettings() : array
    {
        $option_name = '_rvx_settings_widget';
        $reviewx_settings = \get_option($option_name, \false);
        if ($reviewx_settings === \false) {
            $reviewx_settings = \get_option('_rvx_settings_data');
        }
        return $reviewx_settings['setting']['widget_settings'] ?? [];
    }
    /**
     * Upadte Settings Data
     * @return array
     */
    public function updateSettingsData(array $data, $post_type = null) : void
    {
        \update_option("_rvx_settings_data", $data);
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
        \update_option($option_name, $data);
    }
    public function updateReviewSettingsOnSync(array $review_settings, $post_type = null) : void
    {
        $review_settings = $this->preserveStoredMulticriteria($review_settings, $post_type, \true);
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
        \update_option($option_name, $data);
    }
    public function preserveStoredMulticriteria(array $review_settings, $post_type = null, bool $preserveWhenIncomingIsEmpty = \false) : array
    {
        $post_type = $this->normalizeReviewPostType($post_type);
        $existing_settings = $this->getReviewSettings($post_type);
        $existing_multicriteria = $existing_settings['reviews']['multicriteria'] ?? null;
        if (!$this->hasMeaningfulMulticriteria($existing_multicriteria)) {
            return $review_settings;
        }
        if (isset($review_settings['reviews']) && \is_array($review_settings['reviews'])) {
            $incoming_multicriteria = $review_settings['reviews']['multicriteria'] ?? null;
            if (!\array_key_exists('multicriteria', $review_settings['reviews']) || $preserveWhenIncomingIsEmpty && !$this->hasMeaningfulMulticriteria($incoming_multicriteria)) {
                $review_settings['reviews']['multicriteria'] = $existing_multicriteria;
            }
            return $review_settings;
        }
        $incoming_multicriteria = $review_settings['multicriteria'] ?? null;
        if (!\array_key_exists('multicriteria', $review_settings) || $preserveWhenIncomingIsEmpty && !$this->hasMeaningfulMulticriteria($incoming_multicriteria)) {
            $review_settings['multicriteria'] = $existing_multicriteria;
        }
        return $review_settings;
    }
    public function updateWidgetSettings(array $widget_settings) : void
    {
        $data = ["setting" => ["widget_settings" => $widget_settings]];
        \update_option("_rvx_settings_widget", $data);
    }
    public function hasMeaningfulReviewSettings($review_settings) : bool
    {
        if (!\is_array($review_settings) || $review_settings === []) {
            return \false;
        }
        $reviews = isset($review_settings['reviews']) && \is_array($review_settings['reviews']) ? $review_settings['reviews'] : $review_settings;
        return $reviews !== [];
    }
    public function hasMeaningfulWidgetSettings($widget_settings) : bool
    {
        return \is_array($widget_settings) && $widget_settings !== [];
    }
    public function mergeSettingsWithLocalPreference(array $fallback_settings, array $local_settings) : array
    {
        return \array_replace_recursive($fallback_settings, $local_settings);
    }
    public function mergeReviewSettingsForSync(array $fallback_settings, array $local_settings) : array
    {
        $fallback_settings = isset($fallback_settings['reviews']) && \is_array($fallback_settings['reviews']) ? $fallback_settings : ['reviews' => $fallback_settings];
        $local_settings = isset($local_settings['reviews']) && \is_array($local_settings['reviews']) ? $local_settings : ['reviews' => $local_settings];
        $merged_settings = \array_replace_recursive($fallback_settings, $local_settings);
        $local_multicriteria = $local_settings['reviews']['multicriteria'] ?? null;
        if (\is_array($local_multicriteria)) {
            $merged_settings['reviews']['multicriteria'] = \array_replace_recursive($fallback_settings['reviews']['multicriteria'] ?? [], $local_multicriteria);
            foreach (['criterias', 'criteria_key', 'criteria_value'] as $field) {
                if (\array_key_exists($field, $local_multicriteria)) {
                    $merged_settings['reviews']['multicriteria'][$field] = $local_multicriteria[$field];
                }
            }
        }
        return $merged_settings;
    }
    public function syncWooCommerceOptionsFromReviewSettings(array $review_settings, $post_type = 'product') : void
    {
        if ($this->normalizeReviewPostType($post_type) !== 'product') {
            return;
        }
        $reviews = isset($review_settings['reviews']) && \is_array($review_settings['reviews']) ? $review_settings['reviews'] : $review_settings;
        if (\array_key_exists('show_verified_badge', $reviews)) {
            \update_option('woocommerce_review_rating_verification_label', $reviews['show_verified_badge'] ? 'yes' : 'no');
        }
        if (isset($reviews['review_submission_policy']['options']['verified_customer'])) {
            \update_option('woocommerce_review_rating_verification_required', $reviews['review_submission_policy']['options']['verified_customer'] ? 'yes' : 'no');
        }
    }
    private function formatSettings(array $review_settings, array $widget_settings) : array
    {
        $data = ["setting" => ["review_settings" => $review_settings, "widget_settings" => $widget_settings]];
        return $data ?? [];
    }
    public function wooCommerceVerificationRating() : array
    {
        $value = \get_option('woocommerce_review_rating_verification_label', 'no');
        return ['active' => $value === 'yes'];
    }
    public function wooVerificationRatingRequired() : array
    {
        $value = \get_option('woocommerce_review_rating_verification_required', 'no');
        return ['active' => $value === 'yes'];
    }
    public function wooCommerceVerificationRatingUpdate($data)
    {
        if ($data['active'] == \true) {
            \update_option('woocommerce_review_rating_verification_label', 'yes');
            $data = ['success' => \true, 'message' => \__("Verified Owner Active", 'reviewx')];
            return $data;
        }
        if ($data['active'] == \false) {
            \update_option('woocommerce_review_rating_verification_label', 'no');
            $data = ['success' => \true, 'message' => \__("Verified Owner Deactive", 'reviewx')];
            return $data;
        }
    }
    public function wooVerificationRating($data)
    {
        if ($data['active'] == \true) {
            \update_option('woocommerce_review_rating_verification_required', 'yes');
            $data = ['success' => \true, 'message' => \__("Reviews can only be left by verified owners active", 'reviewx')];
            return $data;
        }
        if ($data['active'] == \false) {
            \update_option('woocommerce_review_rating_verification_required', 'no');
            $data = ['success' => \true, 'message' => \__("Reviews can only be left by verified owners deactive", 'reviewx')];
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
        $settings = $data['settings'] ?? null;
        if (\is_array($settings)) {
            $payload_json = \json_encode($settings);
            \update_option('rvx_all_setting_data', $payload_json);
        }
        return ['message' => \__('Settings saved successfully', 'reviewx'), 'data' => $settings];
    }
    public function removeCredentials($requestData)
    {
        global $wpdb;
        $rvxSites = esc_sql($wpdb->prefix . 'rvx_sites');
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Rare cleanup operation on plugin-owned table
        $result = $wpdb->query('TRUNCATE TABLE `' . $rvxSites . '`');
        // Clear general cache if any site ID exists.
        // We don't know the UIDs here so we can't clear specific site caches easily,
        // but TRUNCATE is a rare operation.
        \wp_cache_flush();
        // Drastic but TRUNCATE is also drastic.
        if ($wpdb->last_error) {
            return ['message' => 'Error: ' . $wpdb->last_error];
        }
        return ['message' => 'Site Table deleted successfully', 'result' => $result];
    }
    public function updateSiteData($requestHeaders)
    {
        global $wpdb;
        $table_name = esc_sql($wpdb->prefix . 'rvx_sites');
        // --- Extract headers (headers come as arrays) ---
        $user_email = isset($requestHeaders['x_user_email'][0]) ? sanitize_email($requestHeaders['x_user_email'][0]) : '';
        $user_name = isset($requestHeaders['x_user_name'][0]) ? \sanitize_text_field($requestHeaders['x_user_name'][0]) : '';
        $site_uid = isset($requestHeaders['x_site_uid'][0]) ? \sanitize_text_field($requestHeaders['x_site_uid'][0]) : '';
        // $site_id    = isset($requestHeaders['x_site_id'][0])    ? intval($requestHeaders['x_site_id'][0])                 : 0;
        // $domain     = isset($requestHeaders['x_domain'][0])     ? sanitize_text_field($requestHeaders['x_domain'][0])     : '';
        // --- Validation: must provide at least one field to update ---
        if (empty($user_email) && empty($user_name)) {
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
        // --- Fetch existing row to detect no-op updates and to ensure the row exists ---
        $where_column = \array_key_first($where);
        $where_value = null;
        if (\is_string($where_column) && \array_key_exists($where_column, $where)) {
            $where_value = $where[$where_column];
        }
        if (!\is_string($where_column) || null === $where_value) {
            return ['status' => 'fail', 'message' => 'Invalid where clause columns.'];
        }
        $cache_key = 'rvx_site_' . \md5($where_column . ':' . (string) $where_value);
        $existing = \wp_cache_get($cache_key, 'reviewx');
        if (\false === $existing) {
            $query = null;
            switch ($where_column) {
                case 'uid':
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Custom table name is dynamic but escaped/sanitized previously
                    $query = $wpdb->prepare('SELECT * FROM `' . $table_name . '` WHERE uid = %s LIMIT 1', (string) $where_value);
                    break;
                case 'id':
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Custom table name is dynamic but escaped/sanitized previously
                    $query = $wpdb->prepare('SELECT * FROM `' . $table_name . '` WHERE id = %d LIMIT 1', (int) $where_value);
                    break;
                case 'domain':
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Custom table name is dynamic but escaped/sanitized previously
                    $query = $wpdb->prepare('SELECT * FROM `' . $table_name . '` WHERE domain = %s LIMIT 1', (string) $where_value);
                    break;
                default:
                    return ['status' => 'fail', 'message' => 'Invalid where clause columns.'];
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query, caching implemented above
            $existing = $wpdb->get_row($query, ARRAY_A);
            \wp_cache_set($cache_key, $existing, 'reviewx', 3600);
        }
        if (null === $existing) {
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table update, no standard WP API available
        $updated = $wpdb->update($table_name, $data, $where, $format, $where_fmt);
        // Clear cache
        \wp_cache_delete($cache_key, 'reviewx');
        if (!empty($site_uid)) {
            \wp_cache_delete('rvx_site_uid_' . $site_uid, 'reviewx');
            \wp_cache_delete('rvx_site_exists_' . $site_uid, 'reviewx');
        }
        if ($wpdb->last_error) {
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
    private function normalizeReviewPostType($post_type = null) : string
    {
        return $post_type !== null ? (string) $post_type : 'product';
    }
    private function hasMeaningfulMulticriteria($multicriteria) : bool
    {
        if (!\is_array($multicriteria) || $multicriteria === []) {
            return \false;
        }
        if (\array_key_exists('enable', $multicriteria)) {
            return \true;
        }
        return !empty($multicriteria['criterias']) || !empty($multicriteria['criteria_key']) || !empty($multicriteria['criteria_value']);
    }
}
