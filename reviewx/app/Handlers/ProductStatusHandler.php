<?php

namespace Rvx\Handlers;

class ProductStatusHandler
{
    public function __construct()
    {
    }
    public function __invoke($new_status, $old_status, $post)
    {
        if ($post->post_type === 'product') {
            // Check if the new status is 'publish' or 'publish'
            if (($new_status === 'publish' || $new_status === 'pending') && $old_status !== 'publish') {
                // Product is being published
                // Your custom code for product publishing here
                echo "Product with ID {$post->ID} published.";
            } elseif ($new_status === 'draft' && $old_status === 'publish') {
                // Product is being unpublished
                // Your custom code for product unpublishing here
                echo "Product with ID {$post->ID} unpublished.";
            }
        }
    }
}
