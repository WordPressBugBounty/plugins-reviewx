<?php

namespace Rvx\Handlers;

class ImportProductHandler
{
    public function __invoke($new_status, $old_status, $product)
    {
        if ($new_status == 'publish' && $old_status != 'publish') {
            $product = get_post($product->ID);
            if ($product->post_type === 'product') {
                switch ($new_status) {
                    case 'publish':
                        $currentProduct = wc_get_product($product->ID);
                        $payload = $this->prepareImportedData($currentProduct);
                        $this->appendToJsonl($payload, 'imported_product.jsonl');
                        (new \Rvx\Handlers\ImportProductHandler())->productJsonlFileRead();
                        break;
                }
            }
        }
    }
    public function appendToJsonl($payload, $file_name = 'imported_product.jsonl')
    {
        $file_path = RVX_DIR_PATH . $file_name;
        $json_data = \json_encode($payload) . \PHP_EOL;
        $file_handle = \fopen($file_path, 'a');
        if ($file_handle) {
            \fwrite($file_handle, $json_data);
            \fclose($file_handle);
        }
    }
    public function prepareImportedData($product)
    {
        $data = ['rid' => 'rid://Product/' . $product->ID, 'wp_id' => $product->get_id(), 'title' => $product->get_name(), 'url' => get_permalink($product->get_id()), 'description' => $product->get_short_description(), 'slug' => $product->get_slug(), 'image' => wp_get_attachment_url($product->get_image_id()), 'status' => $this->productStatus($product->get_status()), 'post_type' => 'product', 'total_reviews' => 0, 'price' => $product->get_price() ?? 0, 'avg_rating' => 0.0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->getProductCategories($product)];
        return $data;
    }
    public function productPrepareForSync($product)
    {
        return \array_merge((array) $product, ["category_wp_unique_ids" => $this->getProductCategories($product->wp_id)]);
    }
    public function getProductCategories($product_id)
    {
        $terms = wp_get_post_terms($product_id, 'product_cat');
        $categories = [];
        if (!empty($terms) && !\is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[] = \Rvx\Utilities\Auth\Client::getUid() . '-' . $term->term_id;
            }
        }
        return $categories;
    }
    public function productStatus($status)
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'trash':
                return 2;
            default:
                return 3;
        }
    }
    public function productJsonlFileRead()
    {
        $url = RVX_DIR_PATH . 'imported_product.jsonl';
        $payload = [];
        if (!\file_exists($url)) {
            return;
        }
        $fp = @\fopen($url, "r");
        if ($fp) {
            while (($buffer = \fgets($fp)) !== \false) {
                $result = \json_decode($buffer);
                $payload = $this->productPrepareForSync($result);
                $this->appendToJsonl($payload, 'product_sync.jsonl');
                \error_log("Json" . \print_r($result, \true));
                // if (!isset($result['rid'])) {
                //     return;
                // }
            }
            if (!\feof($fp)) {
                echo "Error: unexpected fgets() fail\n";
            }
            \fclose($fp);
        }
    }
}
