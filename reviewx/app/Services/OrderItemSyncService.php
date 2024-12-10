<?php

namespace Rvx\Services;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class OrderItemSyncService extends \Rvx\Services\Service
{
    protected $orderFullfillmentStatusRelation;
    protected $orderItemCount = 0;
    protected $orderFullfillmentAtRelation;
    protected $orderItemOrderRelation = [];
    protected $orderItemProductRelation = [];
    protected $orderItemQtyRelation = [];
    protected $orderItemPriceRelation = [];
    public function syncOrder($file) : int
    {
        $orderCount = 0;
        $this->orderStat();
        $startDate = (new \DateTime())->modify('-60 days')->format('Y-m-d H:i:s');
        $endDate = (new \DateTime())->format('Y-m-d H:i:s');
        DB::table('wc_orders')->whereBetween('date_created_gmt', $startDate, $endDate)->chunk(100, function ($orders) use($file, &$orderCount) {
            foreach ($orders as $order) {
                $order->fulfillment_status = $this->orderFullfillmentStatusRelation[(int) $order->id];
                $order->fulfilled_at = $this->orderFullfillmentAtRelation[(int) $order->id];
                $formattedOrder = $this->formatOrderData($order);
                Helper::appendToJsonl($file, $formattedOrder);
                $orderCount++;
            }
        });
        return $orderCount;
    }
    public function formatOrderData($order) : array
    {
        $paid_at = null;
        if ($order->fulfillment_status === 'processing') {
            $paid_at = $order->fulfilled_at;
        }
        return ['rid' => 'rid://Order/' . $order->id, "wp_id" => (int) $order->id, "customer_id" => $order->customer_id ? Client::getUid() . '-' . $order->customer_id : null, "subtotal" => (float) $order->total_amount, "tax" => (float) $order->tax_amount, "total" => (float) $order->total_amount, "status" => Helper::rvxGetOrderStatus($order->status), "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "paid_at" => $paid_at, 'created_at' => $order->date_created_gmt, 'updated_at' => $order->date_updated_gmt];
    }
    public function formatOrderItem($orderItem) : array
    {
        return ['rid' => 'rid://LineItem/' . $orderItem->order_item_id, 'wp_id' => (int) $orderItem->order_item_id, 'order_id' => (int) $orderItem->order_id, 'product_wp_unique_id' => Client::getUid() . '-' . $orderItem->product_id, 'name' => $orderItem->order_item_name, 'quantity' => (int) $orderItem->quantity, 'price' => (float) $orderItem->price, 'review_id' => null, 'site_id' => Client::getSiteId(), 'fulfillment_status' => $this->orderFullfillmentStatusRelation[(int) $orderItem->order_id], 'fulfilled_at' => $this->orderFullfillmentAtRelation[(int) $orderItem->order_id], 'reviewed_at' => null];
    }
    public function orderStat()
    {
        $startDate = (new \DateTime())->modify('-60 days')->format('Y-m-d H:i:s');
        $endDate = (new \DateTime())->format('Y-m-d H:i:s');
        DB::table('wc_order_stats')->whereBetween('date_created', $startDate, $endDate)->chunk(100, function ($orderStats) {
            foreach ($orderStats as $orderStat) {
                $data = ['fulfillment_status' => null, 'fulfilled_at' => null];
                if ($orderStat->date_completed) {
                    $data['fulfillment_status'] = Helper::rvxGetOrderStatus($orderStat->status);
                    $data['fulfilled_at'] = $orderStat->date_completed;
                }
                if (!$orderStat->date_completed && $orderStat->date_paid) {
                    $data['fulfillment_status'] = Helper::rvxGetOrderStatus($orderStat->status);
                    $data['fulfilled_at'] = $orderStat->date_paid;
                }
                $this->orderFullfillmentStatusRelation[(int) $orderStat->order_id] = $data['fulfillment_status'];
                $this->orderFullfillmentAtRelation[(int) $orderStat->order_id] = $data['fulfilled_at'];
            }
        });
    }
    public function syncOrderItem($file) : int
    {
        $orderItemCount = 0;
        $this->getOrderItemMeta();
        DB::table('woocommerce_order_items')->whereNotIn('order_item_type', ['shipping'])->chunk(100, function ($orderItems) use($file, &$orderItemCount) {
            foreach ($orderItems as $orderItem) {
                $orderItem->product_id = $this->orderItemProductRelation[$orderItem->order_item_id];
                $orderItem->quantity = $this->orderItemQtyRelation[$orderItem->order_item_id];
                $orderItem->price = $this->orderItemPriceRelation[$orderItem->order_item_id];
                $formattedOrderItem = $this->formatOrderItem($orderItem);
                Helper::appendToJsonl($file, $formattedOrderItem);
                $orderItemCount++;
            }
        });
        return $orderItemCount;
    }
    public function getOrderItemMeta() : void
    {
        DB::table('woocommerce_order_itemmeta')->chunk(100, function ($orderItemMeta) {
            foreach ($orderItemMeta as $item) {
                if ($item->meta_key === '_product_id') {
                    $this->orderItemProductRelation[$item->order_item_id] = $item->meta_value;
                }
                if ($item->meta_key === '_qty') {
                    $this->orderItemQtyRelation[$item->order_item_id] = $item->meta_value;
                }
                if ($item->meta_key === '_line_total') {
                    $this->orderItemPriceRelation[$item->order_item_id] = $item->meta_value;
                }
            }
        });
    }
}
