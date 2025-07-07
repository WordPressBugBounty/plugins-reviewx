<?php

namespace Rvx\Services;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
use Rvx\Handlers\DataSyncHandler;
class CategorySyncService extends \Rvx\Services\Service
{
    protected $categories;
    protected $taxonomyRelation;
    protected $descriptionRelation;
    protected $parentRelation;
    protected $selectedTerms;
    protected $syncedCategories;
    protected $postTermRelation = [];
    protected $taxonomyTerm;
    protected $datSyncHandler;
    public function __construct()
    {
        $this->datSyncHandler = new DataSyncHandler();
    }
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
                    Helper::rvxLog($formatedTerm);
                    Helper::appendToJsonl($file, $formatedTerm);
                    $catCount++;
                }
            }
        });
        Helper::rvxLog($catCount, "Category Done");
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
                    $this->postTermRelation[$termTaxonomyRelation->object_id] = isset($this->taxonomyTerm[$termTaxonomyRelation->term_taxonomy_id]) ? [Helper::arrayGet($this->taxonomyTerm, $termTaxonomyRelation->term_taxonomy_id, [])] : [];
                }
            }
        });
        $this->setPostTermRelation($this->postTermRelation);
    }
    public function syncTermTaxonomy() : void
    {
        DB::table('term_taxonomy')->select(['term_taxonomy_id', 'term_id', 'taxonomy', 'parent'])->whereIn('taxonomy', $this->datSyncHandler->getProductTaxonomies())->chunk(100, function ($allTermTaxonomy) {
            foreach ($allTermTaxonomy as $termTaxonomy) {
                $this->taxonomyTerm[$termTaxonomy->term_taxonomy_id] = (int) $termTaxonomy->term_id;
                $this->selectedTerms[] = (int) $termTaxonomy->term_id;
                $this->taxonomyRelation[$termTaxonomy->term_id] = $termTaxonomy->taxonomy;
                $this->parentRelation[$termTaxonomy->term_id] = $termTaxonomy->parent;
            }
        });
    }
    private function formatCategoryData($category) : array
    {
        return ['rid' => 'rid://Category/' . (int) $category->term_id, 'wp_id' => (int) $category->term_id, 'title' => $category->name ?? null, 'slug' => $category->slug ?? null, 'taxonomy' => $this->taxonomyRelation[$category->term_id] ?? null, 'description' => null, 'parent_wp_unique_id' => isset($this->parentRelation[$category->term_id]) ? Client::getUid() . '-' . $this->parentRelation[$category->term_id] : ''];
    }
}
