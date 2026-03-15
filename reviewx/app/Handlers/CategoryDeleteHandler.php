<?php

namespace ReviewX\Handlers;

use Exception;
use ReviewX\Api\CategoryApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\CPT\CptHelper;
use ReviewX\WPDrill\Response;
class CategoryDeleteHandler
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
    public function deleteHandler($term_id, $tt_id, $taxonomy, $deleted_term, $object_ids)
    {
        $enabled_post_types = $this->cptHelper->usedCPT('used');
        $related_post_types = $this->taxonomyHandler->getPostTypesByTaxonomy($taxonomy);
        if (empty(\array_intersect(\array_keys($enabled_post_types), $related_post_types))) {
            return;
        }
        $product_related_taxonomies = $this->dataSyncHandler->getProductTaxonomies();
        // Only proceed if it is a taxonomy object of 'product' post type
        if (empty(\array_intersect([$taxonomy], $product_related_taxonomies))) {
            return;
        }
        $this->syncTermDelete($term_id);
        // if ($this->taxonomyHandler->termParentChanged($term, $taxonomy)) {
        //     $this->handleHierarchyUpdates($deleted_term);
        // }
    }
    protected function syncTermDelete($term_id)
    {
        try {
            $uid = Client::getUid() . '-' . $term_id;
            $response = (new CategoryApi())->remove($uid);
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
        if ($this->taxonomyHandler->isParentTerm($term->term_id, $term->term_taxonomy)) {
            $this->handleNewParentTerm($term);
        } elseif ($this->taxonomyHandler->hadChildrenBeforeUpdate($term->term_id, $term->term_taxonomy)) {
            $this->handleFormerParentTerm($term);
        }
        // Case 3: Term with children moved in hierarchy
        if ($this->taxonomyHandler->hasChildren($term->term_id, $term->term_taxonomy)) {
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
        $descendants = $this->taxonomyHandler->getAllDescendants($term->term_id, $term->term_taxonomy);
        foreach ($descendants as $descendant) {
            $this->taxonomyHandler->syncTermUpdate($descendant['term']);
        }
    }
}
