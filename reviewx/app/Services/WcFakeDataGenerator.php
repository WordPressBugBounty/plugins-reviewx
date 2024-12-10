<?php

namespace Rvx\Services;

if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class WcFakeDataGenerator
{
    /**
     * Main method to generate full WooCommerce demo data.
     *
     * @param int $product_count Number of products to create.
     * @param int $review_count Number of reviews to create.
     * @param int $customer_count Number of customers to create.
     * @param int $order_count Number of orders to create.
     */
    public function generate_demo_data($product_count = 100, $review_count = 500, $customer_count = 50, $order_count = 300)
    {
        echo 'Demo data generation started.<br/>';
        $categories = $this->generate_categories();
        $product_ids = $this->generate_products($product_count, $categories);
        $this->generate_reviews($product_ids, $review_count);
        $customer_ids = $this->generate_customers($customer_count);
        $this->generate_orders($product_ids, $customer_ids, $order_count);
        echo 'Demo data generation completed.<br/>';
        echo <<<HTML
Data Generated:<br/>
Products: {$product_count}<br/>
Categories: {count({$categories})}<br/>
Reviews: {$review_count}<br/>
Customers: {$customer_count}<br/>
Orders: {$order_count}<br/>
HTML;
    }
    /**
     * Generate WooCommerce categories.
     *
     * @return array List of category IDs.
     */
    private function generate_categories()
    {
        $categories = [];
        $category_names = ['Electronics', 'Books', 'Clothing', 'Accessories', 'Toys', 'Furniture'];
        foreach ($category_names as $name) {
            $term = \Rvx\wp_insert_term($name, 'product_cat');
            if (!is_wp_error($term)) {
                $categories[] = $term['term_id'];
            }
        }
        return $categories;
    }
    /**
     * Generate WooCommerce products and assign to categories.
     *
     * @param int $count Number of products to generate.
     * @param array $categories List of category IDs.
     * @return array List of product IDs.
     */
    private function generate_products($count, $categories)
    {
        $product_ids = [];
        for ($i = 0; $i < $count; $i++) {
            $product = new \Rvx\WC_Product_Simple();
            $product->set_name('Test Product ' . \uniqid());
            $product->set_regular_price(\mt_rand(10, 1000));
            $product->set_description('This is a test product description.');
            $product->set_short_description('Short description of test product.');
            $product->set_sku('SKU-' . \uniqid());
            $product->set_stock_status('instock');
            $product->set_manage_stock(\true);
            $product->set_stock_quantity(\mt_rand(1, 100));
            // Assign to random category
            if (!empty($categories)) {
                $product->set_category_ids([$categories[\array_rand($categories)]]);
            }
            $product_id = $product->save();
            $product_ids[] = $product_id;
        }
        return $product_ids;
    }
    /**
     * Generate WooCommerce reviews and assign them to products.
     */
    private function generate_reviews($product_ids, $count)
    {
        if (empty($product_ids)) {
            return;
        }
        $names = ['John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Brown', 'Charlie Davis'];
        $comments = ['Amazing product!', 'Great value for the price.', 'Would definitely recommend.', 'Not what I expected.', 'Will buy again for sure.'];
        for ($i = 0; $i < $count; $i++) {
            $product_id = $product_ids[\array_rand($product_ids)];
            $comment_data = ['comment_post_ID' => $product_id, 'comment_author' => $names[\array_rand($names)], 'comment_content' => $comments[\array_rand($comments)], 'comment_type' => 'review', 'comment_approved' => 1];
            $comment_id = \Rvx\wp_insert_comment($comment_data);
            if ($comment_id) {
                \Rvx\update_comment_meta($comment_id, 'rating', \mt_rand(1, 5));
            }
        }
    }
    /**
     * Generate WooCommerce customers.
     *
     * @param int $count Number of customers to generate.
     * @return array List of customer IDs.
     */
    private function generate_customers($count)
    {
        $customer_ids = [];
        for ($i = 0; $i < $count; $i++) {
            $email = 'user' . \uniqid() . '@example.com';
            $user_id = \Rvx\wp_create_user($email, 'password', $email);
            if (!is_wp_error($user_id)) {
                \Rvx\wp_update_user(['ID' => $user_id, 'role' => 'customer']);
                $customer_ids[] = $user_id;
            }
        }
        return $customer_ids;
    }
    /**
     * Generate WooCommerce orders and assign to customers and products.
     */
    private function generate_orders($product_ids, $customer_ids, $count)
    {
        if (empty($product_ids) || empty($customer_ids)) {
            return;
        }
        $statuses = ['completed', 'processing', 'on-hold'];
        for ($i = 0; $i < $count; $i++) {
            $order = \Rvx\wc_create_order();
            // Add random products to order
            $product_id = $product_ids[\array_rand($product_ids)];
            $order->add_product(\Rvx\wc_get_product($product_id), \mt_rand(1, 5));
            // Assign to random customer
            $customer_id = $customer_ids[\array_rand($customer_ids)];
            $order->set_customer_id($customer_id);
            // Set random status
            $order->set_status($statuses[\array_rand($statuses)]);
            $order->calculate_totals();
            $order->save();
        }
    }
}
// Example usage
// Uncomment the following lines to run this script within your plugin.
// $generator = new WC_Fake_Data_Generator();
// $generator->generate_demo_data(500, 1000, 100, 300);
