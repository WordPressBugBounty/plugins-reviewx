<?php

namespace Rvx\Handlers\RvxInit;

class UpgradeReviewxDeactiveProHandler
{
    public function __invoke($upgrader_object, $options)
    {
        if ($options['type'] === 'plugin' && isset($options['plugins'])) {
            // Your plugin's main file
            $reviewxFilePath = plugin_basename(__FILE__);
            // Check if your plugin is being updated
            if (\in_array($reviewxFilePath, $options['plugins'], \true)) {
                $reviewxProDeactive = 'reviewx-pro/reviewx-pro.php';
                // Path to the plugin to deactivate
                // Check if the target plugin is active
                if (is_plugin_active($reviewxProDeactive)) {
                    deactivate_plugins($reviewxProDeactive);
                    // Deactivate the plugin
                }
            }
        }
    }
}
