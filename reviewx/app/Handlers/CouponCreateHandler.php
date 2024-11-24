<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
class CouponCreateHandler implements InvokableContract
{
    public function __invoke($coupon)
    {
        \error_log('copon id' . $coupon);
    }
}
