<?php

namespace Rvx\Handlers;

use Rvx\Services\OrderService;
class OrderUpdateHandler
{
    protected $orderService;
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    public function __invoke($order_id)
    {
        $transient_key = 'order_updated_' . $order_id;
        if (\false === \get_transient($transient_key)) {
            set_transient($transient_key, \true, 30);
            wp_schedule_single_event(\time() + 5, 'process_order_update', array($order_id));
        }
    }
}
