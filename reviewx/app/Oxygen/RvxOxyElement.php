<?php

namespace Rvx\Oxygen;

if (\class_exists('RvxOxyElement')) {
    return;
}
class RvxOxyElement
{
    function __construct()
    {
        $this->load_files();
    }
    function load_files()
    {
        // Single Product
        include_once "elements/rvx-stats.class.php";
        include_once "elements/rvx-summary.class.php";
        $element_filenames = \glob(plugin_dir_path(__FILE__) . "elements/*.php");
        foreach ($element_filenames as $filename) {
            include_once $filename;
        }
    }
}
