<?php

namespace Rvx\Handlers\WcTemplates;

class WcOrderTableProductImageHandler
{
    public function __invoke($order)
    {
        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);
            if ($product) {
                $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                if ($image_url) {
                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '" style="width:50px; height:50px;" />';
                }
            }
        }
    }
}
