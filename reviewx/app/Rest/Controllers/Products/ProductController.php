<?php

namespace Rvx\Rest\Controllers\Products;

use Rvx\Services\ProductService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class ProductController implements InvokableContract
{
    protected $productService;
    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        //        $this->schedule_product_chunk_processing();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function selectable($request)
    {
        $resp = $this->productService->getSelectProduct($request->get_params());
        return Helper::getApiResponse($resp);
    }
    //    public function get_products_chunk($offset, $limit) {
    //        global $wpdb;
    //
    //        $query = $wpdb->prepare("
    //             SELECT *
    //        FROM {$wpdb->posts}
    //        WHERE post_type = 'product'
    //        LIMIT %d OFFSET %d
    //    ", $limit, $offset);
    //
    //        return $wpdb->get_results($query);
    //    }
    //    public function wpProducts()
    //    {
    //        global $wpdb;
    ////        $args = array(
    ////            'post_type' => 'product',
    ////            'posts_per_page' => -1,
    ////        );
    ////
    ////        $query = new WP_Query($args);
    ////        $product_count = $query->found_posts;
    ////
    ////        update_option('rvx_product_count', $product_count);
    //
    //        $chunk_size = 100;
    //        $offset = get_option('product_chunk_offset', 0);
    //
    //        $products = $this->get_products_chunk($offset, $chunk_size);
    //
    //        $total_products = $offset + count($products);
    //        update_option('total_products_count', $total_products);
    //        error_log('total product'. $total_products);
    //        update_option('product_chunk_offset', $offset + $chunk_size);
    //        error_log('chank size'. $offset);
    //        if (count($products) < $chunk_size) {
    //            delete_option('product_chunk_offset');
    //
    //        }
    //    }
    //    public function schedule_product_chunk_processing() {
    //        if (!wp_next_scheduled('$this->wpProducts()')) {
    //            wp_schedule_event(time(), 'hourly', '$this->wpProducts()');
    //        }
    //    }
}
