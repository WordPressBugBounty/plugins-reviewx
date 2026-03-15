<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class ImportExpotHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/import-export', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
