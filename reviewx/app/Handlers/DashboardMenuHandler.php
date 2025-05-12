<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
class DashboardMenuHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/dashboard', ['title' => 'Welcome Deshboard', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
