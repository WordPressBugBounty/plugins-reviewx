<?php

namespace ReviewX\Handlers;

use Exception;
use ReviewX\Api\CategoryApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\CPT\CptHelper;
use ReviewX\WPDrill\Response;
class CategoryUpdateHandler
{
    protected $cptHelper;
    protected $taxonomyHandler;
    protected $dataSyncHandler;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
        $this->taxonomyHandler = new \ReviewX\Handlers\TaxonomyHandler();
        $this->dataSyncHandler = new \ReviewX\Handlers\DataSyncHandler();
    }
    public function __invoke($term_id)
    {
        $enabled_post_types = $this->cptHelper->usedCPT('used');
        $related_post_types = $this->taxonomyHandler->getPostTypesByTermId($term_id);
        if (empty(\array_intersect(\array_keys($enabled_post_types), $related_post_types))) {
            return;
        }
        global $wpdb;
        $taxonomy = $this->taxonomyHandler->getTaxonomyByTermIdFromDB($term_id, $wpdb);
        $this_taxonomy_name = $taxonomy[0]['taxonomy'] ?? '';
        $product_related_taxonomies = $this->dataSyncHandler->getProductTaxonomies();
        // Only proceed if it is a taxonomy object of 'product' post type
        if (empty(\array_intersect([$this_taxonomy_name], $product_related_taxonomies))) {
            return;
        }
        $term = $this->taxonomyHandler->getTerm($term_id, $this_taxonomy_name);
        if (!$term) {
            return;
        }
        $this->syncTermUpdate($term);
        // if ($this->taxonomyHandler->termParentChanged($term_id, $this_taxonomy_name)) {
        //     $this->handleHierarchyUpdates($term);
        // }
    }
    protected function syncTermUpdate($term)
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
    protected function handleHierarchyUpdates($term)
    {
        // Case 1: Term became a parent
        if ($this->taxonomyHandler->isParentTerm($term->term_id, $term->taxonomy)) {
            $this->handleNewParentTerm($term);
        } elseif ($this->taxonomyHandler->hadChildrenBeforeUpdate($term->term_id, $term->taxonomy)) {
            $this->handleFormerParentTerm($term);
        }
        // Case 3: Term with children moved in hierarchy
        if ($this->taxonomyHandler->hasChildren($term->term_id, $term->taxonomy)) {
            $this->updateAllDescendants($term);
        }
    }
    protected function handleNewParentTerm($term)
    {
        // Add specific new parent logic here
    }
    protected function handleFormerParentTerm($term)
    {
        // Add specific former parent logic here
    }
    protected function updateAllDescendants($term)
    {
        $descendants = $this->taxonomyHandler->getAllDescendants($term->term_id, $term->taxonomy);
        foreach ($descendants as $descendant) {
            $this->syncTermUpdate($descendant['term']);
        }
    }
}
