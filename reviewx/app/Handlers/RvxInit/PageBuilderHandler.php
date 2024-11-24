<?php

namespace Rvx\Handlers\RvxInit;

use Rvx\Oxygen\OxygenLoad;
use Rvx\RvxDivi\RvxDivi;
class PageBuilderHandler
{
    public function __invoke()
    {
        if (\class_exists('Rvx\\Elementor\\Plugin')) {
            \Rvx\Elementor\Classes\Starter::instance();
        }
        if (\class_exists('Rvx\\CT_Component')) {
            (new OxygenLoad())->rvx_oxygen_woocommerce_init();
        }
        new RvxDivi();
    }
}
