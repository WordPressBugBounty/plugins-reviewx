<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\Handlers\MigrationRollback\SharedMethods;
class MigrationPrompt
{
    public function rvx_retrieve_old_plugin_options_data()
    {
        $data = [];
        $sharedMethods = new SharedMethods();
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
        if ($sharedMethods->key_exists('_rx_option_allow_img')) {
            $data['photo_reviews_allowed'] = get_option('_rx_option_allow_img');
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
        if (empty($data)) {
            return \false;
        }
        return $data;
    }
}
