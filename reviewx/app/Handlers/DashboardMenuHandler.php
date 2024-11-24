<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Facades\Config;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\DB;
use Rvx\WPDrill\Facades\View;
class DashboardMenuHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('dashboard', ['title' => 'Welcome Deshboard', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
