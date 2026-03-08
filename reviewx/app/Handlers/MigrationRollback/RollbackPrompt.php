<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\Services\SettingService;
class RollbackPrompt
{
    // Constructor
    public function __construct()
    {
    }
    // Entry point for the rollback process
    public function rvx_retrieve_sass_plugin_reviews_meta_updater()
    {
        \printf('<h3>%s</h3>', \esc_html__('Rollback started.', 'reviewx'));
        // Rollback options data
        $this->rvx_retrieve_saas_plugin_options_data();
        \printf('%s<br>', \esc_html__('Options data rollback completed.', 'reviewx'));
        // Rollback multi-criteria reviews
        $reviews_data = $this->rvx_retrieve_saas_plugin_criterias_reviews_converter();
        \printf('%s<br>', \esc_html__('Multi-criteria data rollback completed.', 'reviewx'));
        // Rollback attachments for reviews
        $this->rvx_retrieve_saas_plugin_reviews_attachments_data_converter($reviews_data);
        \printf('%s<br>', \esc_html__('Reviews attachments data rollback completed.', 'reviewx'));
        \printf('<h3>%s</h3><br>', \esc_html__('Rollback done.', 'reviewx'));
    }
    // Handles the rollback of plugin options data
    public function rvx_retrieve_saas_plugin_options_data()
    {
        $settings_data = (array) (new SettingService())->getSettingsData()['setting'] ?? [];
        // If settings data is an array, extract and update widget/review settings
        if (\is_array($settings_data)) {
            $widget_settings = $settings_data['widget_settings'] ?? [];
            $review_settings = $settings_data['review_settings']['reviews'] ?? [];
            $this->update_widget_settings($widget_settings);
            $this->update_review_settings($review_settings);
        }
        $sharedMethods = new \Rvx\Handlers\MigrationRollback\SharedMethods();
        // Convert multi-criteria data if available
        if (isset($existing_data['reviews']['multicriteria'])) {
            $oldCriteriaData = $sharedMethods->rvxRollbackReverseReviewCriteriaConverter($existing_data['reviews']['multicriteria']);
            if ($sharedMethods->key_exists('_rx_option_review_criteria')) {
                \update_option('_rx_option_allow_multi_criteria', $oldCriteriaData['_rx_option_allow_multi_criteria']);
                \update_option('_rx_option_review_criteria', $oldCriteriaData['_rx_option_review_criteria']);
            }
            return $oldCriteriaData;
        }
        return \true;
    }
    // Updates widget-related settings
    private function update_widget_settings($widget_settings)
    {
        if (isset($widget_settings['brand_color_code'])) {
            \update_option('_rx_option_color_theme', $widget_settings['brand_color_code']);
        }
        if (isset($widget_settings['star_color_code'])) {
            \update_option('_rx_option_star_color', $widget_settings['star_color_code']);
        }
        if (isset($widget_settings['button_font_color_code'])) {
            \update_option('_rx_option_button_font_color', $widget_settings['button_font_color_code']);
        }
    }
    // Updates review-related settings
    private function update_review_settings($review_settings)
    {
        $map = ['enable_likes_dislikes' => '_rx_option_allow_like_dislike', 'photo_reviews_allowed' => '_rx_option_allow_img', 'video_reviews_allowed' => '_rx_option_allow_video', 'anonymous_reviews_allowed' => '_rx_option_allow_anonymouse', 'allow_multiple_reviews' => '_rx_option_allow_multiple_review'];
        // Update each mapped setting
        foreach ($map as $key => $option) {
            if (isset($review_settings[$key])) {
                \update_option($option, $review_settings[$key]);
            }
        }
        // Handle boolean values for auto-approve and schema settings
        if (isset($review_settings['auto_approve_reviews'])) {
            \update_option('_rx_option_disable_auto_approval', !$review_settings['auto_approve_reviews']);
        }
        if (isset($review_settings['product_schema'])) {
            \update_option('_rx_option_disable_richschema', !$review_settings['product_schema']);
        }
    }
    // Handles rollback for multi-criteria reviews
    public function rvx_retrieve_saas_plugin_criterias_reviews_converter()
    {
        $reviews_with_meta = $this->rvx_retrieve_saas_plugin_reviews_data();
        if (empty($reviews_with_meta)) {
            return [];
        }
        $existingOldData = \get_option('_rx_option_review_criteria');
        $oldCriteria = \is_string($existingOldData) ? \maybe_unserialize($existingOldData) : [];
        if (empty($oldCriteria)) {
            return [];
        }
        // Process and convert each review's criteria
        foreach ($reviews_with_meta as $comment_id => $review_data) {
            $criterias = $review_data['meta_data']['rvx_criterias'] ?? null;
            if (\is_array($criterias)) {
                $converted_criterias = $this->rvx_convert_criterias_to_serialized_format($criterias, $oldCriteria);
                \update_comment_meta($comment_id, 'rvx_criterias', $converted_criterias);
            }
        }
        return $reviews_with_meta;
    }
    // Retrieves reviews with their metadata
    public function rvx_retrieve_saas_plugin_reviews_data()
    {
        global $wpdb;
        $meta_key = 'rvx_review_version';
        $meta_value = 'v2';
        $cache_key = 'rvx_rollback_comment_ids';
        $comment_ids = \wp_cache_get($cache_key, 'reviewx');
        if (\false === $comment_ids) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Fetching comment IDs by meta; no WP API equivalent for this query
            $comment_ids = $wpdb->get_col($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value));
            \wp_cache_set($cache_key, $comment_ids, 'reviewx', 3600);
        }
        if (empty($comment_ids)) {
            return [];
        }
        $placeholders = \implode(',', \array_fill(0, \count($comment_ids), '%d'));
        $comment_ids_int = \array_map('intval', $comment_ids);
        $cache_key_data = 'rvx_rollback_reviews_data_' . \md5(\implode(',', $comment_ids_int));
        $reviews_data = \wp_cache_get($cache_key_data, 'reviewx');
        if (\false === $reviews_data) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Bulk fetching comments by IDs for rollback
            $reviews_data = $wpdb->get_results(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $placeholders is a safe array_fill of %d
                $wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_ID IN ({$placeholders})", $comment_ids_int),
                ARRAY_A
            );
            \wp_cache_set($cache_key_data, $reviews_data, 'reviewx', 3600);
        }
        $cache_key_meta = 'rvx_rollback_meta_data_' . \md5(\implode(',', $comment_ids_int));
        $meta_data = \wp_cache_get($cache_key_meta, 'reviewx');
        if (\false === $meta_data) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Bulk fetching comment meta for rollback
            $meta_data = $wpdb->get_results(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $placeholders is a safe array_fill of %d
                $wpdb->prepare("SELECT comment_id, meta_key, meta_value FROM {$wpdb->commentmeta} WHERE comment_id IN ({$placeholders})", $comment_ids_int),
                ARRAY_A
            );
            \wp_cache_set($cache_key_meta, $meta_data, 'reviewx', 3600);
        }
        $reviews_with_meta = [];
        foreach ($reviews_data as $review) {
            $comment_id = $review['comment_ID'];
            $reviews_with_meta[$comment_id] = ['review_data' => $review, 'meta_data' => []];
        }
        foreach ($meta_data as $meta) {
            $comment_id = $meta['comment_id'];
            if (isset($reviews_with_meta[$comment_id])) {
                $reviews_with_meta[$comment_id]['meta_data'][$meta['meta_key']] = \maybe_unserialize($meta['meta_value']);
            }
        }
        return $reviews_with_meta;
    }
    // Converts criteria data into serialized format
    private function rvx_convert_criterias_to_serialized_format($criterias, $oldCriteria)
    {
        $key_base = 'ctr_h8S';
        $serialized_array = [];
        $index = 7;
        foreach ($criterias as $key => $value) {
            $serialized_key = $key_base . $index++;
            $serialized_array[$serialized_key] = (string) $value;
        }
        return maybe_serialize($serialized_array);
    }
    // Converts review attachments to match the required rollback format
    public function rvx_retrieve_saas_plugin_reviews_attachments_data_converter($reviews_data)
    {
        if (!\is_array($reviews_data)) {
            return;
        }
        foreach ($reviews_data as $review_data) {
            $comment_id = $review_data['review_data']['comment_ID'] ?? null;
            $attachments = $review_data['meta_data']['reviewx_attachments'] ?? null;
            if (\is_array($attachments)) {
                $attachment_data = [];
                foreach ($attachments as $attachment_url) {
                    if (\is_string($attachment_url)) {
                        $attachment_id = attachment_url_to_postid($attachment_url);
                        if ($attachment_id) {
                            $attachment_data[] = $attachment_id;
                        }
                    }
                }
                if (!empty($attachment_data)) {
                    $attachment_data_collection = ['images' => $attachment_data];
                    \update_comment_meta($comment_id, 'reviewx_attachments', $attachment_data_collection);
                }
            }
        }
    }
}
