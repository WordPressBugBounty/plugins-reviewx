<?php

namespace Rvx\Handlers\WcTemplates;

class WoocommerceCustomColumnsHandler
{
    public function __invoke($columns)
    {
        $columns['rvx-review'] = __('Review', 'reviewx');
        // $columns['rvx-product-image'] = __('Image', 'reviewx');
        return $columns;
    }
}
