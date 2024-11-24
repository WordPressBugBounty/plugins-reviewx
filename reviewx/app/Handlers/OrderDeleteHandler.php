<?php

namespace Rvx\Handlers;

use Rvx\Api\OrderApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class OrderDeleteHandler
{
    public function __construct()
    {
    }
    public function __invoke($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $uid = Client::getUid() . '-' . $order_id;
        $response = (new OrderApi())->delete($uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            \error_log('Delete insert' . $response->getStatusCode());
            return \false;
        }
    }
}
