<?php

namespace Rvx\Handlers;

use Rvx\Api\CategoryApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class CategorySync implements InvokableContract
{
    public function __invoke()
    {
        $this->scheduleProductChunkProcessing();
        $this->categoryGat();
    }
    public function categoryGat()
    {
        $chunkSize = 20;
        $offset = get_option('category_chunk_offset', 0);
        $products = $this->getProductsChunk($offset, $chunkSize);
        $modyFiedCategories = [];
        foreach ($products as $category) {
            $modyFiedCategories[] = $this->processCategories($category);
        }
        $this->catSync($modyFiedCategories);
        $totalProducts = $offset + \count($products);
        update_option('total_catagory_count', $totalProducts);
        \error_log('Total Category=: ' . $totalProducts);
        update_option('category_chunk_offset', $offset + $chunkSize);
        \error_log('Chunk size=: ' . $offset);
        if (\count($products) < $chunkSize) {
            delete_option('category_chunk_offset');
        }
    }
    public function processCategories($category)
    {
        if (!empty($category)) {
            return ['wp_id' => (int) $category->term_id, 'title' => $category->name, 'slug' => $category->slug, 'description' => $category->description ?? '', 'taxonomy' => 'product_cat', "parent_id" => null, 'parent_wp_unique_id' => null];
        }
    }
    private function getProductsChunk($offset, $limit)
    {
        global $wpdb;
        $query = $wpdb->prepare("\n            SELECT *\n            FROM {$wpdb->terms}\n            LIMIT %d OFFSET %d\n        ", $limit, $offset);
        return $wpdb->get_results($query);
    }
    public function catSync($payload)
    {
        if (!Client::getSync()) {
            $response = (new CategoryApi())->dataSync(['categories' => $payload]);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                \error_log("Sync Category not inserted: " . $response);
                return \false;
            }
        }
    }
    public function scheduleProductChunkProcessing()
    {
        if (!wp_next_scheduled('category_sync_event')) {
            wp_schedule_event(\time(), 'hourly', 'category_sync_event');
        }
    }
}
