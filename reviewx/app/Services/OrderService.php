<?php

namespace Rvx\Services;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class OrderService extends \Rvx\Services\Service
{
    public function __construct()
    {
    }
    public function updateOrder($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $payload = $this->prepareData($order);
        $uid = Client::getUid() . '-' . $order_id;
        $response = (new OrderApi())->update($payload, $uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log('Order Not Update' . $response->getStatusCode());
            return \false;
        }
    }
    public function prepareData($order)
    {
        $status = $order->get_status();
        $status_mapping = $this->orderStatusArray();
        $created_at = $order->get_date_created() ? \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_created()->getTimestamp())) : null;
        $updated_at = \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_modified()->getTimestamp())) ?? \wp_date('Y-m-d H:i:s');
        // Get the order state, ensure a fallback if 'date_paid' doesn't exist
        $paid_at = $this->wooOrderState($order->get_id()) ?? null;
        $orderData = ["wp_id" => (int) $order->get_id(), "customer_id" => (int) $order->get_customer_id(), "subtotal" => (float) $order->get_subtotal(), "tax" => (float) $order->get_total_tax(), "total" => (float) $order->get_total(), "status" => $order->get_status(), "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "paid_at" => $paid_at, "created_at" => $created_at, "updated_at" => $updated_at];
        if (isset($status_mapping[$status])) {
            $orderData[$status_mapping[$status]] = \wp_date('Y-m-d H:i:s');
        }
        return ['order' => $orderData, 'order_items' => $this->orderItems($order, $orderData)];
    }
    public function orderStatusArray() : array
    {
        return ['processing' => 'processing_at', 'pending_payment' => 'pending_payment_at', 'on_hold' => 'on_hold_at', 'completed' => 'completed_at', 'cancelled' => 'cancelled_at', 'refunded' => 'refunded_at', 'failed' => 'failed_at', 'draft' => 'draft_at'];
    }
    public function wooOrderState($order_id)
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $results = $wpdb->get_row($query);
        if ($results) {
            return ['date_paid' => \wp_date('Y-m-d H:i:s', \strtotime($results->date_paid)) ?? null, 'date_completed' => \wp_date('Y-m-d H:i:s', \strtotime($results->date_completed)) ?? null];
        }
    }
    public function orderItems($order, $orderData = [])
    {
        $date = $this->wooOrderState($order->get_id());
        $items_data = [];
        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            $product = $order_item->get_product();
            if ($product) {
                $item_data = ["wp_id" => (int) $order_item->get_id(), "wp_unique_id" => Client::getUid() . '-' . (int) $order_item->get_id(), "product_wp_unique_id" => Client::getUid() . '-' . (int) $product->get_id(), "review_id" => null, "site_id" => Client::getSiteId(), "name" => $product->get_name(), "quantity" => $order_item->get_quantity(), "price" => (float) $product->get_price(), "reviewed_at" => null, "fulfillment_status" => $order->get_status()];
                if ($order->get_status() !== 'completed') {
                    $item_data['fulfilled_at'] = \wp_date('Y-m-d H:i:s');
                }
                if ($order->get_status() == 'completed') {
                    $item_data['fulfilled_at'] = \wp_date('Y-m-d H:i:s', \strtotime($date['date_completed']));
                }
                $items_data[] = $item_data;
            }
        }
        return $items_data;
    }
}
