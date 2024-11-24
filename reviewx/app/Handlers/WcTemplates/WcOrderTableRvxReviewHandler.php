<?php

namespace Rvx\Handlers\WcTemplates;

class WcOrderTableRvxReviewHandler
{
    public function __invoke($order)
    {
        $order_id = $order->get_id();
        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $product_id = $item->get_product_id();
            $button_url = add_query_arg(array('order_id' => $order_id, 'product_id' => $product_id, 'item_id' => $item_id), site_url('/your-custom-template/'));
            echo '<a href="' . esc_url($button_url) . '" class="woocommerce-button button">' . __('View Details', 'your-text-domain') . '</a>';
        }
    }
}
