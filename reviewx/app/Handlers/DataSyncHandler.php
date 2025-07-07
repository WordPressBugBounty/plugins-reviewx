<?php

namespace Rvx\Handlers;

class DataSyncHandler
{
    public function wc_data_exists_in_db() : bool
    {
        global $wpdb;
        // Use UNION ALL to reduce multiple queries to a single call
        $query = "\n            SELECT 'product' AS type FROM {$wpdb->posts} WHERE post_type = 'product' LIMIT 1\n            UNION ALL\n            SELECT 'shop_order' FROM {$wpdb->posts} WHERE post_type = 'shop_order' LIMIT 1\n            UNION ALL\n            SELECT 'taxonomy' FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ('product_cat', 'product_tag') LIMIT 1\n        ";
        $results = $wpdb->get_col($query);
        return !empty($results);
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
        return $wpdb->get_col("\n            SELECT DISTINCT tt.taxonomy\n            FROM {$wpdb->term_relationships} tr\n            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id\n            INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID\n            WHERE p.post_type = 'product'\n        ") ?: [];
    }
}
