<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class OrderStatusChangedHandler
{
    public function __invoke($order_id, $old_status, $new_status, $order)
    {
        $payload = $this->prepareData($order, $new_status);
        $uid = Client::getUid() . '-' . $order_id;
        $response = (new OrderApi())->changeStatus($payload, $uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log('Order Not Update' . $response->getStatusCode());
            return \false;
        }
    }
    public function prepareData($order, $new_status) : array
    {
        $orderStatusToTimestampKey = $this->orderStatusToTimestampKey($new_status);
        $current_time = \wp_date('Y-m-d H:i:s');
        $created_at = $order->get_date_created() ? \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_created()->getTimestamp())) : null;
        $updated_at = \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_modified()->getTimestamp())) ?? \wp_date('Y-m-d H:i:s');
        $orderStatusData = ["status" => $new_status, $orderStatusToTimestampKey => $current_time];
        $orderData = ["wp_id" => (int) $order->get_id(), "customer_id" => (int) $order->get_customer_id(), "subtotal" => (float) $order->get_subtotal(), "tax" => (float) $order->get_total_tax(), "total" => (float) $order->get_total(), 'created_at' => $created_at, 'updated_at' => $updated_at];
        $modifiedOrder = \array_merge($orderData, $orderStatusData);
        return ['order' => $modifiedOrder, 'order_items' => $this->orderItems($order, $orderStatusToTimestampKey, $orderStatusData)];
    }
    public function wooOrderState($order)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $wpWcOrderStats = $wpdb->get_row($query);
        $data = [];
        $data['fulfillment_status'] = $order->get_status() ?? null;
        $data['fulfilled_at'] = $wpWcOrderStats->date_completed ?? null;
        return $data;
    }
    public function orderItems($order, $orderStatusToTimestampKey, $orderStatusData) : array
    {
        $data = $this->wooOrderState($order);
        $items_data = [];
        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            $product = $order_item->get_product();
            if ($product) {
                if ('completed' == $orderStatusData['status']) {
                    $item_data = ["wp_unique_id" => Client::getUid() . '-' . (int) $order_item->get_id(), 'fulfillment_status' => $data['fulfillment_status'] ?? null, 'fulfilled_at' => $data['fulfilled_at'] ?? null];
                } else {
                    $item_data = ["wp_unique_id" => Client::getUid() . '-' . (int) $order_item->get_id(), 'fulfillment_status' => $orderStatusData['status'], 'fulfilled_at' => $orderStatusData[$orderStatusToTimestampKey]];
                }
                $items_data[] = $item_data;
            }
        }
        return $items_data;
    }
    public function orderStatusToTimestampKey($newStatus) : string
    {
        $statusMap = ['processing' => 'processing_at', 'pending' => 'pending_payment_at', 'on-hold' => 'on_hold_at', 'completed' => 'completed_at', 'cancelled' => 'cancelled_at', 'refunded' => 'refunded_at', 'failed' => 'failed_at', 'checkout-draft' => 'draft_at'];
        return $statusMap[$newStatus];
    }
}
