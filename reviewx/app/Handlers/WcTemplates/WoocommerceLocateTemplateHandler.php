<?php

namespace Rvx\Handlers\WcTemplates;

class WoocommerceLocateTemplateHandler
{
    public function __invoke($template, $template_name, $template_path)
    {
        $plugin_path = RVX_DIR_PATH . 'woocommerce/';
        if (\file_exists($plugin_path . $template_name)) {
            $template = $plugin_path . $template_name;
        }
        return $template;
    }
}
