<?php

namespace Rvx\Handlers;

use Rvx\Api\UserApi;
use Rvx\Api\OrderApi;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
use Rvx\Utilities\Auth\Client;
use Exception;
class OrderCreateHandler
{
    public function __invoke($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            if (!$order) {
                return \false;
            }
            update_post_meta($order_id, '_rvx_is_new_order', \true);
            $payload = $this->prepareData($order);
            $response = (new OrderApi())->create($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return \false;
            }
            $customers = $this->customerPayload($order);
            $response = (new UserApi())->create($customers);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return \false;
            }
            return \true;
        } catch (Exception $e) {
            return \false;
        }
    }
    public function prepareData($order)
    {
        $paid_at = !empty($order->fulfilled_at) ? $order->fulfilled_at : null;
        return ['order' => ["wp_id" => (int) $order->get_id(), "customer_wp_unique_id" => Client::getUid() . '-' . (int) $order->get_customer_id(), "subtotal" => (double) $order->get_subtotal(), "tax" => (double) $order->get_total_tax(), "total" => (double) $order->get_total(), "status" => Helper::orderStatus($order->get_status()), "delivered_at" => null, "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "paid_at" => $paid_at ?? null, "created_at" => \wp_date('Y-m-d H:i:s') ?? null, "updated_at" => \wp_date('Y-m-d H:i:s') ?? null], 'order_items' => $this->orderItems($order)];
    }
    public function orderItems($order)
    {
        global $wpdb;
        $items_data = [];
        $order_items = $order->get_items();
        $order_id = $order->get_id();
        $query = $wpdb->prepare("SELECT date_paid, date_completed FROM {$wpdb->prefix}wc_order_stats WHERE order_id = %d", $order_id);
        $wpWcOrderStats = $wpdb->get_row($query);
        $fulfillment_status = Helper::orderItemStatus($order->get_status()) ?? null;
        foreach ($order_items as $order_item) {
            $product = $order_item->get_product();
            $item_data = ["wp_id" => (int) $order_item->get_id(), "product_wp_unique_id" => Client::getUid() . '-' . (int) $product->get_id(), "review_id" => null, "site_id" => Client::getSiteId(), "name" => $product->get_name(), "quantity" => $order_item->get_quantity(), "price" => (double) $product->get_price(), "reviewed_at" => null, "fulfillment_status" => $fulfillment_status, "fulfilled_at" => $wpWcOrderStats->date_completed ?? null];
            $items_data[] = $item_data;
        }
        return $items_data;
    }
    public function customerPayload($order)
    {
        $customer_id = $order->get_customer_id();
        // if ($customer_id === 0) {
        //     return [];
        // }
        return ['wp_id' => $customer_id, 'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), 'email' => $order->get_billing_email(), 'city' => $order->get_billing_city() ?? '', 'phone' => $order->get_billing_phone() ?? '', 'address' => $order->get_billing_address_1() ?? '', 'country' => $order->get_billing_country() ?? '', 'status' => 1];
    }
}
