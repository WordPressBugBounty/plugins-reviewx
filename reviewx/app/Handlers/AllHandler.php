<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class AllHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storefront/widget/index', ['title' => 'Welcome dfgsdfg sdfsdf', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
