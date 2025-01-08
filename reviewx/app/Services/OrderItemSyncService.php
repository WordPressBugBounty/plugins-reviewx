<?php

namespace Rvx\Services;

use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
use DateTime;
class OrderItemSyncService extends \Rvx\Services\Service
{
    protected $orderFullfillmentStatusRelation;
    protected $validOrderIds = [];
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
        $startDate = (new DateTime())->modify('-60 days')->format('Y-m-d H:i:s');
        $endDate = (new DateTime())->format('Y-m-d H:i:s');
        DB::table('wc_orders')->select(['id', 'customer_id', 'total_amount', 'tax_amount', 'status', 'date_created_gmt', 'date_updated_gmt'])->whereBetween('date_created_gmt', $startDate, $endDate)->chunk(100, function ($orders) use($file, &$orderCount) {
            foreach ($orders as $order) {
                $this->validOrderIds[] = (int) $order->id;
                $order->fulfillment_status = $this->orderFullfillmentStatusRelation[(int) $order->id];
                $order->fulfilled_at = $this->orderFullfillmentAtRelation[(int) $order->id];
                $formattedOrder = $this->formatOrderData($order);
                Helper::appendToJsonl($file, $formattedOrder);
                $orderCount++;
            }
        });
        Helper::rvxLog($orderCount, "Order Done");
        return $orderCount;
    }
    public function formatOrderData($order) : array
    {
        $paid_at = !empty($order->fulfilled_at) && \strtotime($order->fulfilled_at) ? \wp_date('Y-m-d H:i:s', \strtotime($order->fulfilled_at)) : null;
        Helper::rvxLog($paid_at);
        return ['rid' => 'rid://Order/' . (int) $order->id, "wp_id" => (int) $order->id, "customer_id" => $order->customer_id ? Client::getUid() . '-' . $order->customer_id : null, "subtotal" => (float) ($order->total_amount ?? 0.0), "tax" => (float) ($order->tax_amount ?? 0.0), "total" => (float) ($order->total_amount ?? 0.0), "status" => isset($order->status) ? (int) Helper::rvxGetOrderStatus($order->status) : null, "review_request_email_sent_at" => null, "review_reminder_email_sent_at" => null, "photo_review_email_sent_at" => null, "paid_at" => $paid_at, 'created_at' => !empty($order->date_created_gmt) ? \wp_date('Y-m-d H:i:s', \strtotime($order->date_created_gmt)) : null, 'updated_at' => !empty($order->date_updated_gmt) ? \wp_date('Y-m-d H:i:s', \strtotime($order->date_updated_gmt)) : null];
    }
    public function formatOrderItem($orderItem) : array
    {
        return ['rid' => 'rid://LineItem/' . (int) $orderItem->order_item_id, 'wp_id' => (int) $orderItem->order_item_id, 'order_id' => (int) $orderItem->order_id, 'product_wp_unique_id' => Client::getUid() . '-' . (int) $orderItem->product_id, 'name' => $orderItem->order_item_name ?? null, 'quantity' => (int) ($orderItem->quantity ?? 0), 'price' => (float) ($orderItem->price ?? 0.0), 'review_id' => null, 'site_id' => Client::getSiteId(), 'fulfillment_status' => $this->orderFullfillmentStatusRelation[(int) $orderItem->order_id] ?? '', 'fulfilled_at' => !empty($this->orderFullfillmentAtRelation[(int) $orderItem->order_id]) ? \wp_date('Y-m-d H:i:s', \strtotime($this->orderFullfillmentAtRelation[(int) $orderItem->order_id])) : null, 'reviewed_at' => null];
    }
    public function orderStat()
    {
        $startDate = (new DateTime())->modify('-60 days')->format('Y-m-d H:i:s');
        $endDate = (new DateTime())->format('Y-m-d H:i:s');
        DB::table('wc_order_stats')->whereBetween('date_created', $startDate, $endDate)->chunk(100, function ($orderStats) {
            foreach ($orderStats as $orderStat) {
                $data = [];
                if ($orderStat->date_completed) {
                    $data['fulfillment_status'] = Helper::rvxGetOrderStatus($orderStat->status) ?? null;
                    $data['fulfilled_at'] = $orderStat->date_completed ?? null;
                }
                if (!$orderStat->date_completed && $orderStat->date_paid) {
                    $data['fulfillment_status'] = Helper::rvxGetOrderStatus($orderStat->status) ?? null;
                    $data['fulfilled_at'] = $orderStat->date_paid ?? null;
                }
                $this->orderFullfillmentStatusRelation[(int) $orderStat->order_id] = $data['fulfillment_status'] ?? null;
                $this->orderFullfillmentAtRelation[(int) $orderStat->order_id] = $data['fulfilled_at'] ?? null;
            }
        });
    }
    public function syncOrderItem($file) : int
    {
        $orderItemCount = 0;
        $this->getOrderItemMeta();
        DB::table('woocommerce_order_items')->whereNotIn('order_item_type', ['shipping'])->whereIn('order_id', $this->validOrderIds)->chunk(100, function ($orderItems) use($file, &$orderItemCount) {
            foreach ($orderItems as $orderItem) {
                $orderItem->product_id = $this->orderItemProductRelation[$orderItem->order_item_id];
                $orderItem->quantity = $this->orderItemQtyRelation[$orderItem->order_item_id];
                $orderItem->price = $this->orderItemPriceRelation[$orderItem->order_item_id];
                $formattedOrderItem = $this->formatOrderItem($orderItem);
                Helper::appendToJsonl($file, $formattedOrderItem);
                $orderItemCount++;
            }
        });
        Helper::rvxLog($orderItemCount, "Order Item Done");
        return $orderItemCount;
    }
    public function getOrderItemMeta() : void
    {
        DB::table('woocommerce_order_itemmeta')->whereIn('meta_key', ['_product_id', '_qty', '_line_total'])->chunk(100, function ($orderItemMeta) {
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
