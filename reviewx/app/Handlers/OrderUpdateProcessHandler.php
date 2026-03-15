<?php

namespace ReviewX\Handlers;

use ReviewX\Services\OrderService;
class OrderUpdateProcessHandler
{
    protected $orderService;
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    public function __invoke($order_id)
    {
        $this->orderService->updateOrder($order_id);
    }
}
