<?php

namespace Rvx\Shortcodes\Products;

use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class WooReviewsFormShortcode implements ShortcodeContract
{
    public function render(array $attrs, ?string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'post_id' => null, 'product_id' => null, 'graph' => 'on', 'filter' => 'on', 'list' => 'on', 'form' => 'on'], $attrs);
        // Check if both product_id and post_id are provided.
        if (!Client::getSync()) {
            return '<div class="warning">Error: Please complete the synchronization process of ReviewX.</div>';
        }
        if ($attrs['graph'] == 'off' && $attrs['list'] == 'off' && $attrs['form'] == 'off') {
            return '<div class="warning notice notice-error"><b>Error:</b> Please provide one parameter (graph, list, form) value as `on` in the shortcode.</div>';
        }
        if (!empty($attrs['post_id']) && !empty($attrs['product_id'])) {
            return '<div class="warning notice notice-error"><b>Error:</b> Please provide one parameter `post_id` or `product_id`. Both in the same shortcode isn\'t supported.</div>';
        }
        $title = esc_attr($attrs['title']) ?: 'false';
        $type_name = !empty($attrs['product_id']) ? ' product_id="' . esc_attr($attrs['product_id']) . '"' : (!empty($attrs['post_id']) ? ' post_id="' . esc_attr($attrs['post_id']) . '"' : '');
        return do_shortcode('[rvx-review-form title="' . $title . '" filter="' . esc_attr($attrs['filter']) . '" ' . $type_name . ' graph="' . esc_attr($attrs['graph']) . '" list="' . esc_attr($attrs['list']) . '" form="' . esc_attr($attrs['form']) . '"]');
    }
}
