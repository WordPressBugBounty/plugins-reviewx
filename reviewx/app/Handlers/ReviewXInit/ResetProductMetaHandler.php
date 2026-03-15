<?php

namespace ReviewX\Handlers\ReviewXInit;

use ReviewX\Services\Api\LoginService;
class ResetProductMetaHandler
{
    public function __invoke($upgrader_object, $options)
    {
        (new LoginService())->resetPostMeta();
    }
}
