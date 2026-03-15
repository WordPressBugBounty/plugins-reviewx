<?php

namespace ReviewX\Api;

use ReviewX\Apiz\Http\Response;
class WebhookRequestApi extends \ReviewX\Api\BaseApi
{
    /**
     * @param array $data
     * @return Response
     */
    public function finishedWebhook(array $data) : Response
    {
        return $this->withJson($data)->post('webhooks/datasync/finished');
    }
}
