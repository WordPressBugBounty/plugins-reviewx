<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Facades\Config;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\DB;
use Rvx\WPDrill\Facades\View;
class DiscountHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('discount', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}