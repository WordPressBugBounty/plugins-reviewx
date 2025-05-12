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
        echo '<h3>Rollback started.</h3>';
        // Rollback options data
        $this->rvx_retrieve_saas_plugin_options_data();
        echo 'Options data rollback completed.<br>';
        // Rollback multi-criteria reviews
        $reviews_data = $this->rvx_retrieve_saas_plugin_criterias_reviews_converter();
        echo 'Multi-criteria data rollback completed.<br>';
        // Rollback attachments for reviews
        $this->rvx_retrieve_saas_plugin_reviews_attachments_data_converter($reviews_data);
        echo 'Reviews attachments data rollback completed.<br>';
        echo '<h3>Rollback done.</h3><br>';
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
                update_option('_rx_option_allow_multi_criteria', $oldCriteriaData['_rx_option_allow_multi_criteria']);
                update_option('_rx_option_review_criteria', $oldCriteriaData['_rx_option_review_criteria']);
            }
            return $oldCriteriaData;
        }
        return \true;
    }
    // Updates widget-related settings
    private function update_widget_settings($widget_settings)
    {
        if (isset($widget_settings['brand_color_code'])) {
            update_option('_rx_option_color_theme', $widget_settings['brand_color_code']);
        }
        if (isset($widget_settings['star_color_code'])) {
            update_option('_rx_option_star_color', $widget_settings['star_color_code']);
        }
        if (isset($widget_settings['button_font_color_code'])) {
            update_option('_rx_option_button_font_color', $widget_settings['button_font_color_code']);
        }
    }
    // Updates review-related settings
    private function update_review_settings($review_settings)
    {
        $map = ['enable_likes_dislikes' => '_rx_option_allow_like_dislike', 'photo_reviews_allowed' => '_rx_option_allow_img', 'video_reviews_allowed' => '_rx_option_allow_video', 'anonymous_reviews_allowed' => '_rx_option_allow_anonymouse', 'allow_multiple_reviews' => '_rx_option_allow_multiple_review'];
        // Update each mapped setting
        foreach ($map as $key => $option) {
            if (isset($review_settings[$key])) {
                update_option($option, $review_settings[$key]);
            }
        }
        // Handle boolean values for auto-approve and schema settings
        if (isset($review_settings['auto_approve_reviews'])) {
            update_option('_rx_option_disable_auto_approval', !$review_settings['auto_approve_reviews']);
        }
        if (isset($review_settings['product_schema'])) {
            update_option('_rx_option_disable_richschema', !$review_settings['product_schema']);
        }
    }
    // Handles rollback for multi-criteria reviews
    public function rvx_retrieve_saas_plugin_criterias_reviews_converter()
    {
        $reviews_with_meta = $this->rvx_retrieve_saas_plugin_reviews_data();
        if (empty($reviews_with_meta)) {
            return [];
        }
        $existingOldData = get_option('_rx_option_review_criteria');
        $oldCriteria = \is_string($existingOldData) ? maybe_unserialize($existingOldData) : [];
        if (empty($oldCriteria)) {
            return [];
        }
        // Process and convert each review's criteria
        foreach ($reviews_with_meta as $comment_id => $review_data) {
            $criterias = $review_data['meta_data']['rvx_criterias'] ?? null;
            if (\is_array($criterias)) {
                $converted_criterias = $this->rvx_convert_criterias_to_serialized_format($criterias, $oldCriteria);
                update_comment_meta($comment_id, 'rvx_criterias', $converted_criterias);
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
        $query = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value);
        $comment_ids = $wpdb->get_col($query);
        if (empty($comment_ids)) {
            return [];
        }
        $placeholders = \implode(',', \array_fill(0, \count($comment_ids), '%d'));
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_ID IN ({$placeholders})", $comment_ids);
        $reviews_data = $wpdb->get_results($query, ARRAY_A);
        $query = $wpdb->prepare("SELECT comment_id, meta_key, meta_value FROM {$wpdb->commentmeta} WHERE comment_id IN ({$placeholders})", $comment_ids);
        $meta_data = $wpdb->get_results($query, ARRAY_A);
        $reviews_with_meta = [];
        foreach ($reviews_data as $review) {
            $comment_id = $review['comment_ID'];
            $reviews_with_meta[$comment_id] = ['review_data' => $review, 'meta_data' => []];
        }
        foreach ($meta_data as $meta) {
            $comment_id = $meta['comment_id'];
            if (isset($reviews_with_meta[$comment_id])) {
                $reviews_with_meta[$comment_id]['meta_data'][$meta['meta_key']] = maybe_unserialize($meta['meta_value']);
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
                    update_comment_meta($comment_id, 'reviewx_attachments', $attachment_data_collection);
                }
            }
        }
    }
}
