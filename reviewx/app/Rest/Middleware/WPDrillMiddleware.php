<?php

namespace Rvx\Rest\Middleware;

use Rvx\Utilities\Auth\Client;
class WPDrillMiddleware
{
    /**
     * @return bool
     */
    public function handle() : bool
    {
        return \true;
    }
}
