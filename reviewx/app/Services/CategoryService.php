<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\WPDrill\Response as drillResponse;
use Rvx\Api\CategoryApi;
use Rvx\Utilities\Helper;
class CategoryService extends \Rvx\Services\Service
{
    /**
     *
     */
    public function __construct()
    {
        //        add_action('save_post', [$this, 'saveProduct'], 10, 1);
    }
    /**
     * @return Response
     */
    public function selectable()
    {
        return (new CategoryApi())->selectable();
    }
    /**
     * @return Response
     */
    public function getCategory()
    {
        return (new CategoryApi())->getCategory();
    }
    /**
     * @return Response
     */
    public function getCategoryAll() : drillResponse
    {
        $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => \false));
        $response = array();
        foreach ($product_categories as $parent_category) {
            $subcategory_array = array();
            $subcategories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => \false, 'parent' => $parent_category->term_id));
            foreach ($subcategories as $subcategory) {
                $subcategory_array[] = array('name' => $subcategory->name, 'id' => $subcategory->term_id);
            }
            $response[] = array('parent' => array('name' => $parent_category->name, 'id' => $parent_category->term_id), 'subcategories' => $subcategory_array);
        }
        return Helper::rest($response)->success("All category list");
    }
    public function storeCategory($data)
    {
        return (new CategoryApi())->create($data);
    }
}
