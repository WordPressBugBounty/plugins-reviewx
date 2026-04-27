<?php

namespace ReviewX\Handlers;

use ReviewX\Api\OrderApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Response;
class OrderDeleteHandler
{
    public function __construct()
    {
    }
    public function __invoke($order_id)
    {
        $order = \wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $uid = Client::getUid() . '-' . $order_id;
        $response = (new OrderApi())->delete($uid);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
    }
}
