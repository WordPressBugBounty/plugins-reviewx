<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\Utilities\TransactionManager;
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
            // WP Operation: Save meta locally. This must happen.
            $this->orderDataSave($order_id, $payload);
            // SaaS Operation: Attempt sync, but don't block return.
            $response = (new OrderApi())->changeStatus($payload, $uid);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                \error_log("Order Status Change Sync Failed for ID {$order_id}: " . $response->getBody());
            }
        }
        if (isset($_GET['page']) && $_GET['page'] === 'wc-orders') {
            $payload = $this->bulkOrderPrepare($order_id, $old_status, $new_status, $order);
            // WP Operation: Save meta locally.
            $this->orderDataSave($order_id, $payload);
            // SaaS Operation: Attempt sync.
            $response = (new OrderApi())->changeBulkStatus($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                \error_log("Bulk Order Status Sync Failed: " . $response->getBody());
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
        $date_created = $order->get_date_created();
        $created_at = $date_created ? \wp_date('Y-m-d H:i:s', $date_created->getTimestamp()) : null;
        $date_modified = $order->get_date_modified();
        $updated_at = $date_modified ? \wp_date('Y-m-d H:i:s', $date_modified->getTimestamp()) : \wp_date('Y-m-d H:i:s');
        $order_state = $this->wooOrderState($order, $new_status, $old_status);
        $orderStatusData = ["status" => Helper::orderStatus($new_status), "paid_at" => $order_state['paid_at'] ?? null];
        if ($orderStatusToTimestampKey !== 'any') {
            $orderStatusData[$orderStatusToTimestampKey] = $current_time;
        }
        $orderData = ["wp_id" => (int) $order->get_id(), "customer_wp_unique_id" => Client::getUid() . '-' . (int) $order->get_customer_id(), "subtotal" => (float) $order->get_subtotal(), "tax" => (float) $order->get_total_tax(), "total" => (float) $order->get_total(), 'created_at' => $created_at, 'updated_at' => $updated_at];
        $modifiedOrder = \array_merge($orderData, $orderStatusData);
        return ['order' => $modifiedOrder, 'order_items' => $this->orderItems($order, $orderStatusToTimestampKey, $orderStatusData, $new_status, $old_status)];
    }
    public function wooOrderState($order, $new_status, $old_status)
    {
        global $wpdb;
        $order_id = $order->get_id();
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $wpWcOrderStats = $wpdb->get_row($query);
        $fulfilled_at = $wpWcOrderStats && $wpWcOrderStats->date_completed ? \wp_date('Y-m-d H:i:s', \strtotime($wpWcOrderStats->date_completed)) : null;
        $paid_at = $wpWcOrderStats && $wpWcOrderStats->date_paid ? \wp_date('Y-m-d H:i:s', \strtotime($wpWcOrderStats->date_paid)) : null;
        if ($new_status === 'completed' && !$fulfilled_at) {
            $fulfilled_at = \wp_date('Y-m-d H:i:s');
        }
        if ($new_status === 'processing' && !$paid_at) {
            $paid_at = \wp_date('Y-m-d H:i:s');
        }
        return ['fulfillment_status' => Helper::orderItemStatus($new_status) ?? null, 'fulfilled_at' => $fulfilled_at, 'paid_at' => $paid_at];
    }
    public function orderItems($order, $orderStatusToTimestampKey, $orderStatusData, $new_status, $old_status) : array
    {
        $data = $this->wooOrderState($order, $new_status, $old_status);
        $items_data = [];
        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            $product = $order_item->get_product();
            if ($product) {
                $item_data = ["wp_unique_id" => Client::getUid() . '-' . (int) $order_item->get_id(), 'fulfillment_status' => $data['fulfillment_status'] ?? null, 'fulfilled_at' => $data['fulfilled_at'] ?? null];
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
