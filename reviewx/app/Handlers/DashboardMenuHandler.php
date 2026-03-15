<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class DashboardMenuHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/dashboard', ['title' => 'Welcome Deshboard', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
