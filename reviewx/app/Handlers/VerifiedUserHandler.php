<?php

namespace Rvx\Handlers;

class VerifiedUserHandler
{
    public function __construct()
    {
    }
    public function __invoke($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        update_user_meta($user_id, 'is_verified', 'yes');
    }
}
