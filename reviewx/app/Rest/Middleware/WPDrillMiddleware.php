<?php

namespace ReviewX\Rest\Middleware;

\defined("ABSPATH") || exit;
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
