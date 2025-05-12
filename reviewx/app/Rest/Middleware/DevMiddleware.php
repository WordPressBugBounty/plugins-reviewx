<?php

namespace Rvx\Rest\Middleware;

class DevMiddleware
{
    /**
     * @return bool
     */
    public function handle($request) : bool
    {
        $token = $request->get_params()['token'];
        if ($token === '3745678yfughsert7834yfuhjsfe7834r6uirf78t45') {
            return \true;
        }
        return \false;
    }
}
