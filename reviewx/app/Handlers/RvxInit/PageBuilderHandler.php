<?php

namespace Rvx\Handlers\RvxInit;

class PageBuilderHandler
{
    public function __invoke()
    {
        if (\class_exists('\\Elementor\\Plugin')) {
            \Rvx\Elementor\Classes\Starter::instance();
        }
    }
}
