<?php

namespace ReviewX\Handlers;

use Exception;
use ReviewX\Api\CategoryApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Response;
class TaxonomyHandler
{
    public function getPostTypesByTermId(int $term_id) : array
    {
        $term = \get_term($term_id);
        if (!$term || \is_wp_error($term)) {
            return [];
        }
        // Get the taxonomy of the term
        $taxonomy = $term->taxonomy;
        // Get all post types associated with this taxonomy
        $taxonomy_object = \get_taxonomy($taxonomy);
        if (!$taxonomy_object || \is_wp_error($taxonomy_object)) {
            return [];
        }
        return (array) $taxonomy_object->object_type;
    }
    public function getPostTypesByTaxonomy(string $taxonomy) : array
    {
        if (!$taxonomy || \is_wp_error($taxonomy)) {
            return [];
        }
        // Get all post types associated with this taxonomy
        $taxonomy_object = \get_taxonomy($taxonomy);
        if (!$taxonomy_object || \is_wp_error($taxonomy_object)) {
            return [];
        }
        return (array) $taxonomy_object->object_type;
    }
    public function getTerm(int $term_id, string $taxonomy = '')
    {
        $term = \get_term($term_id, $taxonomy);
        return !$term || \is_wp_error($term) ? null : $term;
    }
    public function getTaxonomyByTermIdFromDB($term_id, $wpdb = null)
    {
        // Ensure $term_id is a valid integer
        if (!\is_int($term_id)) {
            return \false;
        }
        if (!$wpdb) {
            global $wpdb;
        }
        // Use direct SQL to get taxonomies for a term from the DB
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- fallback to direct DB query as get_term_taxonomies is not a standard WP function
        $tax_names = $wpdb->get_col($wpdb->prepare("SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d", $term_id));
        if (empty($tax_names)) {
            return \false;
        }
        $taxonomy_array = [];
        foreach ($tax_names as $tax_name) {
            $taxonomy_data = \get_taxonomy($tax_name);
            if ($taxonomy_data) {
                $taxonomy_array[] = ['taxonomy' => $tax_name, 'label' => $taxonomy_data->labels->name, 'hierarchical' => $taxonomy_data->hierarchical];
            }
        }
        return $taxonomy_array;
    }
    public function getAllDescendants(int $parent_id, string $taxonomy) : array
    {
        $descendants = [];
        $this->collectDescendants($parent_id, $taxonomy, $descendants);
        return $descendants;
    }
    protected function collectDescendants(int $parent_id, string $taxonomy, array &$descendants, int $current_level = 1) : void
    {
        $children = \get_terms(['taxonomy' => $taxonomy, 'parent' => $parent_id, 'hide_empty' => \false, 'fields' => 'all']);
        if (\is_wp_error($children) || empty($children)) {
            return;
        }
        foreach ($children as $child) {
            $descendants[] = ['term' => $child, 'level' => $current_level];
            $this->collectDescendants($child->term_id, $taxonomy, $descendants, $current_level + 1);
        }
    }
    public function hasChildren(int $term_id, string $taxonomy) : bool
    {
        $children = \get_terms(['taxonomy' => $taxonomy, 'parent' => $term_id, 'hide_empty' => \false, 'fields' => 'ids', 'number' => 1]);
        return !\is_wp_error($children) && !empty($children);
    }
    public function isParentTerm(int $term_id, string $taxonomy = '') : bool
    {
        $term = $this->getTerm($term_id, $taxonomy);
        return $term ? $term->parent === 0 : \false;
    }
    public function getChildTerms(int $parent_id, string $taxonomy) : array
    {
        $children = \get_terms(['taxonomy' => $taxonomy, 'parent' => $parent_id, 'hide_empty' => \false, 'fields' => 'all']);
        if (\is_wp_error($children)) {
            return [];
        }
        $child_terms = [];
        foreach ($children as $child) {
            $child_terms[] = ['term' => $child, 'children' => $this->getChildTerms($child->term_id, $taxonomy)];
        }
        return $child_terms;
    }
    public function termParentChanged(int $term_id, string $taxonomy) : bool
    {
        // Requires implementation of previous state tracking
        // Example using transients:
        $previous_parent = \get_transient("term_{$term_id}_previous_parent");
        $current_term = $this->getTerm($term_id, $taxonomy);
        return $previous_parent !== \false && $current_term && $previous_parent != $current_term->parent;
    }
    public function hadChildrenBeforeUpdate(int $term_id, string $taxonomy) : bool
    {
        $transient_key = "term_{$term_id}_had_children";
        $had_children = \get_transient($transient_key);
        \delete_transient($transient_key);
        return $had_children === 'yes';
    }
    public function taxonomyExists(string $taxonomy) : bool
    {
        $cache_key = 'rvx_tax_exists_' . $taxonomy;
        $exists = \wp_cache_get($cache_key, 'reviewx');
        if (\false === $exists) {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- taxonomy cache miss fallback
            $exists = (bool) $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $taxonomy));
            \wp_cache_set($cache_key, (int) $exists, 'reviewx', 86400);
        }
        return $exists;
    }
    public function syncTermUpdate($term)
    {
        try {
            $payload = ['wp_id' => $term->term_id, 'title' => $term->name, 'slug' => $term->slug, 'taxonomy' => $term->taxonomy, 'description' => $term->description, 'parent_wp_unique_id' => $term->parent > 0 ? Client::getUid() . '-' . $term->parent : null, 'updated_at' => \current_time('mysql')];
            $uid = Client::getUid() . '-' . $term->term_id;
            $response = (new CategoryApi())->update($payload, $uid);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception(\esc_html__("API status: ", 'reviewx') . $response->getStatusCode());
            }
        } catch (Exception $e) {
            return \false;
        }
    }
}
