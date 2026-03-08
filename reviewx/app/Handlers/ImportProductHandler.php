<?php

namespace Rvx\Handlers;

class ImportProductHandler
{
    public function __invoke($new_status, $old_status, $product)
    {
        if ($new_status == 'publish' && $old_status != 'publish') {
            $product = \get_post($product->ID);
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
        $json_data = wp_json_encode($payload) . \PHP_EOL;
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        $current = $wp_filesystem->exists($file_path) ? $wp_filesystem->get_contents($file_path) : '';
        $wp_filesystem->put_contents($file_path, $current . $json_data, \FS_CHMOD_FILE);
    }
    public function prepareImportedData($product)
    {
        $data = ['rid' => 'rid://Product/' . $product->get_id(), 'wp_id' => $product->get_id(), 'title' => $product->get_name(), 'url' => get_permalink($product->get_id()), 'description' => $product->get_short_description(), 'slug' => $product->get_slug(), 'image' => \wp_get_attachment_url($product->get_image_id()), 'status' => $this->productStatus($product->get_status()), 'post_type' => 'product', 'total_reviews' => 0, 'price' => $product->get_price() ?? 0, 'avg_rating' => 0.0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "category_wp_unique_ids" => $this->getProductCategories($product->get_id())];
        return $data;
    }
    public function productPrepareForSync($product)
    {
        return \array_merge((array) $product, ["category_wp_unique_ids" => $this->getProductCategories($product->wp_id ?? 0)]);
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
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        if (!$wp_filesystem->exists($url)) {
            return;
        }
        $content = $wp_filesystem->get_contents($url);
        if ($content) {
            $lines = \explode(\PHP_EOL, $content);
            foreach ($lines as $line) {
                if (empty(\trim($line))) {
                    continue;
                }
                $result = \json_decode($line);
                if ($result) {
                    $payload = $this->productPrepareForSync($result);
                    $sync_file = RVX_DIR_PATH . 'product_sync.jsonl';
                    $sync_json = wp_json_encode($payload) . \PHP_EOL;
                    $current_sync = $wp_filesystem->exists($sync_file) ? $wp_filesystem->get_contents($sync_file) : '';
                    $wp_filesystem->put_contents($sync_file, $current_sync . $sync_json, \FS_CHMOD_FILE);
                }
            }
        }
    }
}
