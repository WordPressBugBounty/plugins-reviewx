<?php

namespace Rvx\Handlers\RvxInit;

class ReviewXoldPluginDeactivateHandler
{
    /**
     * Handle the deactivation of the old ReviewX Pro plugin.
     *
     * This checks if the old ReviewX Pro plugin is active and deactivates it.
     */
    public function __invoke()
    {
        $reviewxProPath = 'reviewx-pro/reviewx-pro.php';
        // Path to the plugin to deactivate
        // Check if the plugin is active
        if (is_plugin_active($reviewxProPath)) {
            deactivate_plugins($reviewxProPath);
            // Deactivate the plugin
        }
    }
}
