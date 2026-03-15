<?php

namespace ReviewX\Handlers\WcTemplates;

class WoocommerceCustomColumnsHandler
{
    public function __invoke($columns)
    {
        $columns['reviewx-review'] = \__('Review', 'reviewx');
        // $columns['reviewx-product-image'] = __('Image', 'reviewx');
        return $columns;
    }
}
