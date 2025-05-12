<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Response;
class OrderStatusChangedHandler
{
    public function __invoke($order_id, $old_status, $new_status, $order)
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wc-orders' && $_GET['action'] === 'edit') {
            $is_new_order = get_post_meta($order_id, '_is_new_order', \true);
            if ($is_new_order) {
                // Remove the flag to allow future status changes to trigger this hook
                delete_post_meta($order_id, '_rvx_is_new_order');
                return;
            }
            $payload = $this->prepareData($order, $new_status, $old_status);
            $uid = Client::getUid() . '-' . $order_id;
            $response = (new OrderApi())->changeStatus($payload, $uid);
            $this->orderDataSave($order_id, $payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return \false;
            }
        }
        if (isset($_GET['page']) && $_GET['page'] === 'wc-orders') {
            $payload = $this->bulkOrderPrepare($order_id, $old_status, $new_status, $order);
            $response = (new OrderApi())->changeBulkStatus($payload);
            $this->orderDataSave($order_id, $payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return \false;
            }
        }
    }
    public function bulkOrderPrepare($order_id, $old_status, $new_status, $order)
    {
        return ['status' => Helper::orderStatus($new_status), 'order_wp_unique_ids' => [Client::getUid() . '-' . $order_id]];
    }
    public function prepareData($order, $new_status, $old_status) : array
    {
        $orderStatusToTimestampKey = $this->orderStatusToTimestampKey($new_status);
        $current_time = \wp_date('Y-m-d H:i:s');
        $created_at = $order->get_date_created() ? \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_created()->getTimestamp())) : null;
        $updated_at = \wp_date('Y-m-d H:i:s', \strtotime($order->get_date_modified()->getTimestamp())) ?? \wp_date('Y-m-d H:i:s');
        $orderStatusData = ["status" => Helper::orderStatus($new_status)];
        if ($orderStatusToTimestampKey !== 'any') {
            $orderStatusData[$orderStatusToTimestampKey] = $current_time;
        }
        $orderData = ["wp_id" => (int) $order->get_id(), "customer_id" => (int) $order->get_customer_id(), "subtotal" => (float) $order->get_subtotal(), "tax" => (float) $order->get_total_tax(), "total" => (float) $order->get_total(), 'created_at' => $created_at, 'updated_at' => $updated_at];
        $modifiedOrder = \array_merge($orderData, $orderStatusData);
        return ['order' => $modifiedOrder, 'order_items' => $this->orderItems($order, $orderStatusToTimestampKey, $orderStatusData, $new_status, $old_status)];
    }
    public function wooOrderState($order, $new_status, $old_status)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $wpWcOrderStats = $wpdb->get_row($query);
        if ($old_status !== $new_status) {
            $fulfilled_at = $wpWcOrderStats->date_completed ?? \wp_date('Y-m-d H:i:s');
        }
        $data = [];
        $data['fulfillment_status'] = Helper::orderStatus($order->get_status()) ?? null;
        $data['fulfilled_at'] = $fulfilled_at ?? null;
        return $data;
    }
    public function orderItems($order, $orderStatusToTimestampKey, $orderStatusData, $new_status, $old_status) : array
    {
        $data = $this->wooOrderState($order, $new_status, $old_status);
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
        if (!$statusMap[$newStatus]) {
            return 'any';
        }
        return $statusMap[$newStatus];
    }
    public function orderDataSave($order_id, $data)
    {
        $order_meta = Helper::arrayGet($data, 'order');
        $order_item = Helper::arrayGet($data, 'order_items');
        if (!$order_id) {
            return;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $order->update_meta_data('_rvx_order_value', $order_meta);
        $order->update_meta_data('_rvx_order_item_value', $order_item);
        $order->save();
    }
}
