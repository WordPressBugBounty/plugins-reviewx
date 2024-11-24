<?php

namespace Rvx\Handlers\RvxInit;

class LoadTextDomainHandler
{
    public function __invoke()
    {
        $reviewxProDeactive = 'reviewx-pro/reviewx-pro.php';
        // Path to the plugin to deactivate
        // Check if the plugin is active
        if (is_plugin_active($reviewxProDeactive)) {
            deactivate_plugins($reviewxProDeactive);
            // Deactivate the plugin
        }
        load_plugin_textdomain('reviewx', \false, \dirname(plugin_basename(__FILE__)) . '/languages');
    }
}
