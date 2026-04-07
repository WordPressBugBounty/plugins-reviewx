<?php

namespace ReviewX\Handlers;

class DataSyncHandler
{
    // public function wc_data_exists_in_db(): bool
    // {
    //     global $wpdb;
    //     $query = "
    //         (SELECT 'product' AS type
    //         FROM {$wpdb->posts}
    //         WHERE post_type = 'product'
    //         LIMIT 1)
    //         UNION ALL
    //         (SELECT 'shop_order' AS type
    //         FROM {$wpdb->posts}
    //         WHERE post_type = 'shop_order'
    //         LIMIT 1)
    //         UNION ALL
    //         (SELECT 'taxonomy' AS type
    //         FROM {$wpdb->term_taxonomy}
    //         WHERE taxonomy IN ('product_cat', 'product_tag')
    //         LIMIT 1)
    //     ";
    //     $results = $wpdb->get_col($query);
    //     return !empty($results);
    // }
    public function wc_data_exists_in_db() : bool
    {
        $cache_key = 'rvx_wc_data_exists';
        $cached_result = \wp_cache_get($cache_key, 'reviewx');
        if (\false !== $cached_result) {
            return (bool) $cached_result;
        }
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- result is cached above
        $row = $wpdb->get_row($wpdb->prepare("SELECT \n                    (EXISTS(SELECT 1 FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1)) AS has_product,\n                    (EXISTS(SELECT 1 FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1)) AS has_order,\n                    (EXISTS(SELECT 1 FROM {$wpdb->term_taxonomy} WHERE taxonomy IN (%s, %s) LIMIT 1)) AS has_taxonomy", 'product', 'shop_order', 'product_cat', 'product_tag'), ARRAY_A);
        $exists = \in_array(1, $row, \true);
        // error_log('[ReviewX Debug] wc_data_exists_in_db Result: ' . ($exists ? 'true' : 'false'));
        // if (!$exists) {
        //     error_log('[ReviewX Debug] wc_data_exists_in_db Row Data: ' . print_r($row, true));
        // }
        \wp_cache_set($cache_key, (int) $exists, 'reviewx', 3600);
        // Cache for 1 hour
        return $exists;
    }
    public function getProductTaxonomies() : array
    {
        // Try to get taxonomies via WP API if possible
        if (post_type_exists('product')) {
            $taxonomies = get_object_taxonomies('product', 'names');
            if (!empty($taxonomies)) {
                return $taxonomies;
            }
        }
        // Fallback to DB-only method if API fails or WooCommerce is disabled
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fallback query when API fails
        return $wpdb->get_col($wpdb->prepare("\n            SELECT DISTINCT tt.taxonomy\n            FROM {$wpdb->term_relationships} tr\n            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id\n            INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID\n            WHERE p.post_type = %s\n        ", 'product')) ?: [];
    }
}
