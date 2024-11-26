<?php

namespace Rvx\Oxygen;

use Rvx\Oxygen\RvxOxyElement;
class OxygenLoad
{
    public function rvx_oxygen_woocommerce_init()
    {
        if (!\class_exists('OxygenElement')) {
            return;
        }
        $this->loadRequiredFiles();
        $rvxOxyElement = new RvxOxyElement();
        // $rvxOxyWooEl = new RvxOxyWooEl();
    }
    private function loadRequiredFiles()
    {
        require_once 'RvxOxyWooEl.php';
    }
}
