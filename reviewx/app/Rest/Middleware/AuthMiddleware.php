<?php

namespace Rvx\Rest\Middleware;

use Rvx\Utilities\Auth\Client;
class AuthMiddleware
{
    /**
     * @return bool
     */
    public function handle() : bool
    {
        if (Client::getUid()) {
            return \true;
        }
        return \false;
    }
}
