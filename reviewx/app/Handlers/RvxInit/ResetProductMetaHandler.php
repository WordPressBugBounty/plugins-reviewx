<?php

namespace Rvx\Handlers\RvxInit;

use Rvx\Services\Api\LoginService;
class ResetProductMetaHandler
{
    public function __invoke($upgrader_object, $options)
    {
        (new LoginService())->resetPostMeta();
    }
}
