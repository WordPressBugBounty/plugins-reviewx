<?php

namespace Rvx\Handlers;

use Exception;
use Rvx\Api\CategoryApi;
use Rvx\Utilities\Auth\Client;
use Rvx\CPT\CptHelper;
use Rvx\WPDrill\Response;
class CategoryCreateHandler
{
    protected $cptHelper;
    protected $taxonomyHandler;
    protected $dataSyncHandler;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
        $this->taxonomyHandler = new \Rvx\Handlers\TaxonomyHandler();
        $this->dataSyncHandler = new \Rvx\Handlers\DataSyncHandler();
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
        $this->syncTermCreate($term);
        // if ($this->taxonomyHandler->termParentChanged($term_id, $this_taxonomy_name)) {
        //     $this->handleHierarchyUpdates($term);
        // }
    }
    protected function syncTermCreate($term)
    {
        try {
            $payload = ['wp_id' => $term->term_id, 'title' => $term->name, 'slug' => $term->slug, 'taxonomy' => $term->taxonomy, 'description' => $term->description, 'parent_wp_unique_id' => Client::getUid() . '-' . $term->parent ?? null];
            $response = (new CategoryApi())->create($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception("API status: " . $response->getStatusCode());
            }
        } catch (Exception $e) {
            \error_log("Update failed for term {$term->term_id}: " . $e->getMessage());
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
        "Term {$term->term_id} is now a parent";
        // Add specific new parent logic here
    }
    protected function handleFormerParentTerm($term)
    {
        // error_log("Term {$term->term_id} was a parent but is now a child");
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
