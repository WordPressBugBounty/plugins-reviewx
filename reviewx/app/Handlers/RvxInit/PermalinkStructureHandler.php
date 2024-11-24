<?php

namespace Rvx\Handlers\RvxInit;

use Rvx\WPDrill\Facades\View;
class PermalinkStructureHandler
{
    public function __invoke()
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
    }
}
