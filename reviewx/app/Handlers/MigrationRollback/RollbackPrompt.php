<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\Handlers\MigrationRollback\SharedMethods;
class RollbackPrompt
{
    public function __construct()
    {
        // Constructor code if needed
    }
    public function rvx_retrieve_sass_plugin_reviews_meta_updater()
    {
        // Convert Retrieved v1 Muli Criteria data to v1 format
        echo '<h3>Rollback started.</h3>' . '';
        $this->rvx_retrieve_saas_plugin_options_data();
        echo 'Options data rollback completed.' . '<br>';
        $reviews_data = $this->rvx_retrieve_sass_plugin_criterias_reviews_converter();
        echo 'Multi criteria data rollback completed.' . '<br>';
        $this->rvx_retrieve_saas_plugin_reviews_attachments_data_converter($reviews_data);
        echo 'Reviews attachments data rollback completed.' . '<br>';
        echo '<h3>Rollback done.</h3>' . '<br>';
    }
    public function rvx_retrieve_saas_plugin_options_data()
    {
        // Retrieve the existing new data (JSON) from 'rvx_review_settings'
        $option_key = 'rvx_review_settings';
        $existing_data = get_option($option_key);
        $existing_data = \json_decode($existing_data, \true);
        //dd($existing_data);
        // Return false if JSON data is invalid or not found
        /*
        if (!$existing_data || !is_array($existing_data)) {
            return false;
        }
        */
        // Retrieve additional data from '_rvx_settings_data' (serialized data)
        $serialized_data = get_option('_rvx_settings_data');
        $settings_data = maybe_unserialize($serialized_data);
        if ($settings_data && \is_array($settings_data)) {
            $widget_settings = $settings_data['setting']['widget_settings'] ?? [];
            $review_settings = $settings_data['setting']['review_settings']['reviews'] ?? [];
            // Update old keys in wp_options based on the available data
            if (isset($widget_settings['brand_color_code'])) {
                update_option('_rx_option_color_theme', $widget_settings['brand_color_code']);
            }
            if (isset($widget_settings['star_color_code'])) {
                update_option('_rx_option_star_color', $widget_settings['star_color_code']);
            }
            if (isset($widget_settings['button_font_color_code'])) {
                update_option('_rx_option_button_font_color', $widget_settings['button_font_color_code']);
            }
            if (isset($review_settings['enable_likes_dislikes']['enabled'])) {
                update_option('_rx_option_allow_like_dislike', $review_settings['enable_likes_dislikes']['enabled']);
            }
            if (isset($review_settings['photo_reviews_allowed'])) {
                update_option('_rx_option_allow_img', $review_settings['photo_reviews_allowed']);
            }
            if (isset($review_settings['video_reviews_allowed'])) {
                update_option('_rx_option_allow_video', $review_settings['video_reviews_allowed']);
            }
            if (isset($review_settings['anonymous_reviews_allowed'])) {
                update_option('_rx_option_allow_anonymouse', $review_settings['anonymous_reviews_allowed']);
            }
            if (isset($review_settings['auto_approve_reviews'])) {
                update_option('_rx_option_disable_auto_approval', !$review_settings['auto_approve_reviews']);
                // Reversed logic
            }
            if (isset($review_settings['product_schema'])) {
                update_option('_rx_option_disable_richschema', !$review_settings['product_schema']);
                // Reversed logic
            }
            if (isset($review_settings['allow_multiple_reviews'])) {
                update_option('_rx_option_allow_multiple_review', $review_settings['allow_multiple_reviews']);
            }
        }
        // Also handle specific cases from existing JSON data
        $sharedMethods = new SharedMethods();
        if (isset($existing_data['reviews']['multicriteria'])) {
            $oldCriteriaData = $sharedMethods->rvxRollbackReverseReviewCriteriaConverter($existing_data['reviews']['multicriteria']);
            if ($sharedMethods->key_exists('_rx_option_review_criteria')) {
                //update_option('_rx_option_review_criteria', $oldCriteriaData['_rx_option_review_criteria']);
                update_option('_rx_option_allow_multi_criteria', $oldCriteriaData['_rx_option_allow_multi_criteria']);
            }
            return $oldCriteriaData;
        }
        return \true;
        // Return true to indicate the process was successful
    }
    public function rvx_retrieve_sass_plugin_criterias_reviews_converter()
    {
        // Step 1: Fetch all reviews with rvx_review_version = 'v2'
        $reviews_with_meta = $this->rvx_retrieve_saas_plugin_reviews_data();
        if (empty($reviews_with_meta)) {
            return;
            // No reviews to process
        }
        // Step 2: Fetch _rx_option_review_criteria for matching keys
        $existingOldData = get_option('_rx_option_review_criteria');
        $oldCriteria = [];
        if ($existingOldData) {
            $oldCriteria = maybe_unserialize($existingOldData);
        }
        if (empty($oldCriteria)) {
            return;
            // No criteria mapping available
        }
        // Step 3: Map and update rvx_criterias for each review
        foreach ($reviews_with_meta as $comment_id => $review_data) {
            if (isset($review_data['meta_data']['rvx_criterias']) && \is_array($review_data['meta_data']['rvx_criterias'])) {
                $criterias = $review_data['meta_data']['rvx_criterias'];
                $converted_criterias = $this->rvx_convert_criterias_to_serialized_format($criterias, $oldCriteria);
                // Update the new format back into the database
                update_comment_meta($comment_id, 'rvx_criterias', $converted_criterias);
            }
        }
        return $reviews_with_meta;
    }
    public function rvx_retrieve_saas_plugin_reviews_data()
    {
        global $wpdb;
        // Define the meta key and value
        $meta_key = 'rvx_review_version';
        $meta_value = 'v2';
        // Step 1: Retrieve comment IDs from wp_commentmeta
        $query = $wpdb->prepare("SELECT comment_id \n             FROM {$wpdb->commentmeta} \n             WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value);
        $comment_ids = $wpdb->get_col($query);
        // Return empty array if no comment IDs found
        if (empty($comment_ids)) {
            return [];
        }
        // Step 2: Retrieve all comments data for the retrieved comment IDs
        $placeholders = \implode(',', \array_fill(0, \count($comment_ids), '%d'));
        // Create placeholders for IN clause
        $query = $wpdb->prepare("SELECT * \n             FROM {$wpdb->comments} \n             WHERE comment_ID IN ({$placeholders})", $comment_ids);
        $reviews_data = $wpdb->get_results($query, ARRAY_A);
        // Fetch results as an associative array
        // Step 3: Retrieve all meta data for the retrieved comment IDs
        $query = $wpdb->prepare("SELECT comment_id, meta_key, meta_value \n             FROM {$wpdb->commentmeta} \n             WHERE comment_id IN ({$placeholders})", $comment_ids);
        $meta_data = $wpdb->get_results($query, ARRAY_A);
        // Step 4: Arrange data as a multidimensional array
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
    /**
     * Map new criteria values to correct keys using old criteria.
     *
     * @param array $criterias New criteria values (e.g., ["a" => "4", "b" => "3"]).
     * @param array $oldCriteria Old criteria mapping (e.g., ["ctr_h8S7" => "Quality"]).
     * @return string Serialized criteria data.
     */
    private function rvx_convert_criterias_to_serialized_format($criterias)
    {
        $key_base = 'ctr_h8S';
        $serialized_array = [];
        $index = 7;
        // Start index for serialized keys
        foreach ($criterias as $key => $value) {
            $serialized_key = $key_base . $index++;
            $serialized_array[$serialized_key] = (string) $value;
        }
        return maybe_serialize($serialized_array);
    }
    public function rvx_retrieve_saas_plugin_reviews_attachments_data_converter($reviews_data)
    {
        if (!empty($reviews_data) && \is_array($reviews_data)) {
            foreach ($reviews_data as $review_data) {
                $comment_id = $review_data['review_data']['comment_ID'];
                $attachments = $review_data['meta_data']['reviewx_attachments'];
                if (!empty($attachments) && \is_array($attachments)) {
                    $attachment_data_ids = '';
                    $attachment_data = [];
                    foreach ($attachments as $attachment_url) {
                        $attachment_id = attachment_url_to_postid($attachment_url);
                        if (!empty($attachment_id)) {
                            // Prepare the string data in the desired format
                            $attachment_data_ids .= $attachment_id . ', ';
                            $attachment_data[] = $attachment_id;
                        }
                    }
                    if (!empty($attachment_data) && \is_array($attachment_data)) {
                        // Prepare the serialized data in the desired format
                        $attachment_data_collection = ['images' => $attachment_data];
                        // Update the wp_commentmeta table with the new format
                        update_comment_meta($comment_id, 'reviewx_attachments', $attachment_data_collection);
                        //echo "Comment id: $comment_id => Attachment id: $attachment_data_ids id's updated:";
                    }
                }
            }
        }
    }
}
