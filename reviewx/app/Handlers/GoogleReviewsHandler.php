<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class GoogleReviewsHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/google', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}
