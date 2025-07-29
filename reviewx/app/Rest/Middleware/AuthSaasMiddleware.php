<?php

namespace Rvx\Rest\Middleware;

use Rvx\Utilities\Auth\Client;
class AuthSaasMiddleware
{
    public function handle(\WP_REST_Request $request) : bool
    {
        // ReviewX Used ID check
        return Client::getUid() ? \true : \false;
    }
}
