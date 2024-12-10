<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
class AllHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storefront/widget/index', ['title' => 'Welcome dfgsdfg sdfsdf', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
