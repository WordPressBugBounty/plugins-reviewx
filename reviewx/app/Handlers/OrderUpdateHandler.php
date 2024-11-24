<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\WPDrill\Response;
use Rvx\Services\OrderService;
use Rvx\Utilities\Auth\Client;
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
        if (\false === get_transient($transient_key)) {
            set_transient($transient_key, \true, 30);
            wp_schedule_single_event(\time() + 5, 'process_order_update', array($order_id));
        }
    }
}
