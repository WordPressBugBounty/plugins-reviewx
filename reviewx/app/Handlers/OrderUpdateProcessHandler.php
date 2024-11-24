<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\WPDrill\Response;
use Rvx\Services\OrderService;
use Rvx\Utilities\Auth\Client;
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
