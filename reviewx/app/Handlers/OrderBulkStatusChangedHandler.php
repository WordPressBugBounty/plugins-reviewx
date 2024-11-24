<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\WPDrill\Response;
use Rvx\Services\OrderService;
use Rvx\Utilities\Auth\Client;
class OrderBulkStatusChangedHandler
{
    protected $orderService;
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    public function __invoke($redirect_to, $action, $order_ids)
    {
        if ($action === 'mark_complete') {
            $data = [];
            foreach ($order_ids as $order_id) {
                $data['order_wp_unique_ids'] = $order_id;
            }
            \error_log("Return Data" . \print_r($data, \true));
        }
        return $redirect_to;
    }
}
