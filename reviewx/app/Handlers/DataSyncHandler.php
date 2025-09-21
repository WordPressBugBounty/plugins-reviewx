<?php

namespace Rvx\Handlers;

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
        global $wpdb;
        $query = "\n            SELECT \n                (EXISTS(SELECT 1 FROM {$wpdb->posts} WHERE post_type = 'product' LIMIT 1)) AS has_product,\n                (EXISTS(SELECT 1 FROM {$wpdb->posts} WHERE post_type = 'shop_order' LIMIT 1)) AS has_order,\n                (EXISTS(SELECT 1 FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ('product_cat', 'product_tag') LIMIT 1)) AS has_taxonomy\n        ";
        $row = $wpdb->get_row($query, ARRAY_A);
        return \in_array(1, $row, \true);
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
