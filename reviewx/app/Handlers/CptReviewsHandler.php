<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class CptReviewsHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/cpt', ['cpt' => "cpt"]);
    }
}
