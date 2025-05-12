<?php

namespace Rvx\Rest\Middleware;

use Rvx\Utilities\Auth\Client;
class AdminMiddleware
{
    /**
     * @return bool
     */
    public function handle($request) : bool
    {
        if (Client::getUid() === $request->get_param('uid')) {
            return \true;
        }
        return \false;
    }
}
