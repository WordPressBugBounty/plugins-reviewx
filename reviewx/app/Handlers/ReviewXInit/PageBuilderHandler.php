<?php

namespace ReviewX\Handlers\ReviewXInit;

class PageBuilderHandler
{
    public function __invoke()
    {
        if (\class_exists('\\Elementor\\Plugin')) {
            \ReviewX\Elementor\Classes\Starter::instance();
        }
    }
}
