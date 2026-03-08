<?php

namespace Rvx\Services\Api;

\defined("ABSPATH") || exit;
use Rvx\Api\AuthApi;
use Rvx\Services\Service;
class LoginService extends Service
{
    public function resetPostMeta()
    {
        global $wpdb;
        $insight_key = '_rvx_latest_reviews_insight';
        $review_key = '_rvx_latest_reviews';
        $post_ids = $wpdb->get_col(
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- 2 placeholders, 2 replacements; PHPCS miscounts with OR clause
            $wpdb->prepare("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s OR meta_key = %s", $insight_key, $review_key)
        );
        if (!empty($post_ids)) {
            $post_ids_placeholders = \implode(',', \array_fill(0, \count($post_ids), '%d'));
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk delete of transient post meta
            $wpdb->query($wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_ids_placeholders is a safe array_fill of %d
                "DELETE FROM {$wpdb->postmeta} WHERE (meta_key = %s OR meta_key = %s) AND post_id IN ({$post_ids_placeholders})",
                \array_merge([$insight_key, $review_key], $post_ids)
            ));
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
    public function forgetPassword($data)
    {
        return (new AuthApi())->forgetPassword($data);
    }
    public function resetPassword($data)
    {
        return (new AuthApi())->resetPassword($data);
    }
}
