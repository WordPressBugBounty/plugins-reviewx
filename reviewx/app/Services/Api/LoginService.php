<?php

namespace Rvx\Services\Api;

use Rvx\Apiz\Http\Response;
use Exception;
use Rvx\Api\AuthApi;
use Rvx\Models\Site;
use Rvx\Services\Service;
use Rvx\Utilities\Auth\Client;
class LoginService extends Service
{
    /**
     * @param string $license_key
     * @return Response
     * @throws Exception
     */
    public function loginKey(string $license_key)
    {
        $payload = array('license_key' => sanitize_text_field($license_key));
        return (new AuthApi())->licenseLogin($payload);
    }
    public function resetPostMeta()
    {
        global $wpdb;
        $insight_key = '_rvx_latest_reviews_insight';
        $review_key = '_rvx_latest_reviews';
        $post_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s OR meta_key = %s", $insight_key, $review_key));
        if (!empty($post_ids)) {
            $post_ids_placeholders = \implode(',', \array_fill(0, \count($post_ids), '%d'));
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE (meta_key = %s OR meta_key = %s) AND post_id IN ({$post_ids_placeholders})", \array_merge([$insight_key, $review_key], $post_ids)));
        }
    }
    public function resetProductWisePostMeta($product_id)
    {
        global $wpdb;
        $review_key = '_rvx_latest_reviews';
        // Validate the product ID
        if (empty($product_id) || !\is_numeric($product_id)) {
            return;
            // Exit if the product ID is invalid
        }
        // Prepare and execute the deletion query
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id = %d", $review_key, $product_id));
    }
    /**
     * @param $settings_data
     * @return void
     */
    public function save_site_settings($settings_data)
    {
        $settings = array('brand_color' => $settings_data['brandColor'], 'review_view_style' => $settings_data['reviewViewStyle'], 'multi_criteria' => $settings_data['multiCriteria'] ? 1 : 0, 'allow_photo_review' => $settings_data['allowPhotoReview'] ? 1 : 0, 'auto_review_enabled' => $settings_data['autoReviewReq']['isEnabled'] ? 1 : 0, 'auto_review_criteria_days' => $settings_data['autoReviewReq']['criteria']['days'], 'auto_review_criteria_status' => $settings_data['autoReviewReq']['criteria']['status']);
        foreach ($settings as $option_name => $option_value) {
            update_option($option_name, $option_value);
        }
    }
    public function createSite($site_info)
    {
        $site = Site::where('uid', $site_info['uid'])->first();
        if (!$site) {
            Site::insert($site_info);
        } else {
            Site::where("id", $site->id)->update($site_info);
        }
    }
    public function prepareData($site) : array
    {
        return ['site_id' => $site['id'], 'uid' => $site['uid'], 'name' => $site['name'], 'domain' => $site['domain'], 'url' => $site['url'], 'locale' => $site['locale'], 'email' => $site['email'], 'secret' => $site['key'], 'created_at' => \date('Y-m-d H:i:s', \time()), 'updated_at' => \date('Y-m-d H:i:s', \time())];
    }
    public function forgetPassword($data)
    {
        return (new AuthApi())->forgetPassword($data);
    }
    public function resetPassword($data)
    {
        return (new AuthApi())->resetPassword($data);
    }
}
