<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
class CptReviewsHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/cpt', ['cpt' => "cpt"]);
    }
}
