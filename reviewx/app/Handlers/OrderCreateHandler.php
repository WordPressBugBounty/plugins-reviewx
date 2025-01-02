<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class OrderCreateHandler
{
    public function __construct()
    {
    }
    public function __invoke($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        // $customer_id = $order->get_user_id();
        // update_user_meta( $customer_id, 'customer_verified', true );
        $payload = $this->prepareData($order);
        $response = (new OrderApi())->create($payload);
        \error_log("Order Status " . $order->get_status());
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log('Order Not insert' . $response->getStatusCode());
            return \false;
        }
    }
    public function prepareData($order)
    {
        $created_at = $order->get_date_created() ? \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_created()->getTimestamp())) : null;
        $updated_at = \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_modified()->getTimestamp())) ?? \wp_date('Y-m-d H:i:s');
        return ['order' => ["wp_id" => (int) $order->get_id(), "customer_wp_unique_id" => Client::getUid() . '-' . (int) $order->get_customer_id(), "subtotal" => (double) $order->get_subtotal(), "tax" => (double) $order->get_total_tax(), "total" => (double) $order->get_total(), "status" => $order->get_status(), "delivered_at" => null, "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "created_at" => $created_at, "updated_at" => $updated_at], 'order_items' => $this->orderItems($order)];
    }
    public function orderItems($order)
    {
        $items_data = [];
        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            $product = $order_item->get_product();
            $item_data = ["wp_id" => (int) $order_item->get_id(), "product_wp_unique_id" => Client::getUid() . '-' . (int) $product->get_id(), "review_id" => null, "site_id" => Client::getSiteId(), "name" => $product->get_name(), "quantity" => $order_item->get_quantity(), "price" => (double) $product->get_price(), "reviewed_at" => null];
            $items_data[] = $item_data;
        }
        return $items_data;
    }
}
