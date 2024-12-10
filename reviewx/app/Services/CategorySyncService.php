<?php

namespace Rvx\Services;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class CategorySyncService extends \Rvx\Services\Service
{
    protected $categories;
    protected $categoriesCount;
    protected $taxonomyRelation;
    protected $descriptionRelation;
    protected $parentRelation;
    protected $selectedTerms;
    protected $syncedCategories;
    protected $postTermRelation = [];
    protected $taxonomyTerm;
    public function syncCategory($file)
    {
        $catCount = 0;
        $this->syncTermTaxonomy();
        $this->syncTermTaxonomyRelation();
        DB::table('terms')->chunk(100, function ($allTerms) use($file, &$catCount) {
            foreach ($allTerms as $term) {
                if (\in_array((int) $term->term_id, $this->selectedTerms, \true)) {
                    $formatedTerm = $this->formatCategoryData($term);
                    $this->setSyncCategories($formatedTerm);
                    Helper::appendToJsonl($file, $formatedTerm);
                    $catCount++;
                }
            }
        });
        return $catCount;
    }
    public function getPostTermRelation()
    {
        return $this->postTermRelation;
    }
    public function setSyncCategories($syncedCategories) : void
    {
        $this->syncedCategories[] = $syncedCategories;
    }
    public function setPostTermRelation($postTermRelation)
    {
        return $this->postTermRelation = $postTermRelation;
    }
    public function syncTermTaxonomyRelation() : void
    {
        DB::table('term_relationships')->chunk(100, function ($allTermTaxonomyRelations) {
            foreach ($allTermTaxonomyRelations as $termTaxonomyRelation) {
                if (\array_key_exists($termTaxonomyRelation->object_id, $this->postTermRelation)) {
                    $this->postTermRelation[$termTaxonomyRelation->object_id] = \array_merge($this->postTermRelation[$termTaxonomyRelation->object_id], [(int) $termTaxonomyRelation->term_taxonomy_id]);
                } else {
                    $this->postTermRelation[$termTaxonomyRelation->object_id] = $this->taxonomyTerm[$termTaxonomyRelation->term_taxonomy_id] ? [$this->taxonomyTerm[$this->taxonomyTerm[$termTaxonomyRelation->term_taxonomy_id]]] : [];
                }
            }
        });
        $this->setPostTermRelation($this->postTermRelation);
    }
    public function syncTermTaxonomy() : void
    {
        DB::table('term_taxonomy')->whereIn('taxonomy', ['product_cat', 'category', 'product_type', 'product_visibility'])->chunk(100, function ($allTermTaxonomy) {
            foreach ($allTermTaxonomy as $termTaxonomy) {
                $this->taxonomyTerm[$termTaxonomy->term_taxonomy_id] = (int) $termTaxonomy->term_id;
                $this->selectedTerms[] = (int) $termTaxonomy->term_id;
                $this->taxonomyRelation[$termTaxonomy->term_id] = $termTaxonomy->taxonomy;
                $this->descriptionRelation[$termTaxonomy->term_id] = $termTaxonomy->description;
                $this->parentRelation[$termTaxonomy->term_id] = $termTaxonomy->parent;
            }
        });
    }
    private function formatCategoryData($category) : array
    {
        return ['rid' => 'rid://Category/' . $category->term_id, 'wp_id' => (int) $category->term_id, 'title' => $category->name, 'slug' => $category->slug, 'taxonomy' => $this->taxonomyRelation[$category->term_id], 'description' => $this->descriptionRelation[$category->term_id], 'parent_wp_unique_id' => Client::getUid() . '-' . $this->parentRelation[$category->term_id]];
    }
}
