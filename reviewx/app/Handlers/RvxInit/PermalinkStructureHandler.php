<?php

namespace Rvx\Handlers\RvxInit;

class PermalinkStructureHandler
{
    public function __invoke()
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
    }
}
