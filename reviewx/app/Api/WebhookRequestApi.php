<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class WebhookRequestApi extends \Rvx\Api\BaseApi
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
