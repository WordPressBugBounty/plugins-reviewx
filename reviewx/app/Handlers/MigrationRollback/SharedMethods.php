<?php

namespace Rvx\Handlers\MigrationRollback;

class SharedMethods
{
    public function rvx_is_old_pro_plugin_active()
    {
        // Check for the older ReviewX Pro versions
        $pro_version = \defined('REVIEWX_PRO_VERSION') ? REVIEWX_PRO_VERSION : null;
        if ($pro_version !== null) {
            return \true;
        } else {
            return \false;
        }
    }
    public function rvx_activate_old_pro_plugin()
    {
        // Ensure WordPress functions are available
        if (!\function_exists('Rvx\\get_plugins') || !\function_exists('Rvx\\activate_plugin')) {
            return;
            // Exit if WordPress is not fully loaded
        }
        // Retrieve all installed plugins
        $plugins = get_plugins();
        $found_plugin = '';
        // Search for the ReviewX Pro plugin
        foreach ($plugins as $plugin_path => $plugin_data) {
            if (\strpos($plugin_path, 'reviewx-pro') !== \false && \defined('WP_PLUGIN_DIR') && \file_exists(WP_PLUGIN_DIR . '/' . $plugin_path)) {
                $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
                // Check if the file contains a unique identifier
                $plugin_content = \file_get_contents($plugin_file);
                if (\strpos($plugin_content, 'REVIEWX_PRO_VERSION') !== \false) {
                    $found_plugin = $plugin_path;
                    break;
                }
            }
        }
        // Activate the plugin if it is found and not already active
        if ($found_plugin && !is_plugin_active($found_plugin)) {
            $result = activate_plugin($found_plugin);
            if (\is_wp_error($result)) {
                // Optionally, handle errors if activation fails
            } else {
                // Optionally, display an admin notice for successful activation
            }
        } else {
            // Optionally, display a notice if the plugin is not found
        }
    }
    public function rvx_deactivate_old_pro_plugin()
    {
        if (!\function_exists('Rvx\\get_plugins') || !\function_exists('Rvx\\deactivate_plugins')) {
            return;
            // Exit if WordPress is not fully loaded
        }
        // Retrieve all installed plugins
        $plugins = get_plugins();
        $found_plugin = '';
        // Search for the ReviewX Pro plugin
        foreach ($plugins as $plugin_path => $plugin_data) {
            if (\strpos($plugin_path, 'reviewx-pro') !== \false && \defined('WP_PLUGIN_DIR') && \file_exists(WP_PLUGIN_DIR . '/' . $plugin_path)) {
                $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
                $plugin_content = \file_get_contents($plugin_file);
                if (\strpos($plugin_content, 'REVIEWX_PRO_VERSION') !== \false) {
                    $found_plugin = $plugin_path;
                    break;
                }
            }
        }
        // Deactivate the plugin if it is found and not already deactive
        if ($found_plugin && is_plugin_active($found_plugin)) {
            $result = deactivate_plugins($found_plugin);
            if (\is_wp_error($result)) {
                // Optionally, handle errors if deactivation fails
            } else {
                // Optionally, display an admin notice for successful deactivation
            }
        } else {
            // Optionally, display a notice if the plugin is not found
        }
    }
    public function rvxOldReviewCriteriaConverter()
    {
        $data = get_option('_rx_option_review_criteria');
        $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
        $criterias = [];
        $i = 0;
        foreach ($data as $key => $name) {
            if (isset($keys[$i])) {
                $criterias[] = ["key" => $keys[$i], "name" => $name];
            }
            $i++;
        }
        $multicrtriaEnableorDisable = get_option('_rx_option_allow_multi_criteria');
        $newCriteria = ["enable" => $multicrtriaEnableorDisable == 1 ? \true : \false, "criterias" => $criterias];
        return $newCriteria;
    }
    public function rvxRollbackReverseReviewCriteriaConverter($newCriteria)
    {
        // Initialize old criteria structure
        $oldCriteria = [];
        // Retrieve existing criteria from the database
        $existingOldData = get_option('_rx_option_review_criteria');
        if ($existingOldData) {
            $oldCriteria = maybe_unserialize($existingOldData);
            // Deserialize existing criteria
        }
        // Find the highest number in old criteria keys (ctr_h8S7, ctr_h8S8, etc.)
        $highestNumber = 0;
        foreach ($oldCriteria as $key => $value) {
            if (\preg_match('/ctr_h8S(\\d+)/', $key, $matches)) {
                $highestNumber = \max($highestNumber, (int) $matches[1]);
                // Track the highest number
            }
        }
        // Merge old and new criteria
        $seenValues = \array_flip($oldCriteria);
        // Store old criteria values for fast lookup
        $mergedCriteria = $oldCriteria;
        // Start with old criteria
        // Assign new keys for unique new criteria
        foreach ($newCriteria['criterias'] as $criteria) {
            if (isset($criteria['name']) && !isset($seenValues[$criteria['name']])) {
                $highestNumber++;
                // Increment key number
                $newKey = 'ctr_h8S' . $highestNumber;
                $mergedCriteria[$newKey] = $criteria['name'];
                $seenValues[$criteria['name']] = \true;
                // Mark as seen
            }
        }
        // Build updated data structure
        $updatedData = [
            '_rx_option_allow_multi_criteria' => $newCriteria['enable'] ? 1 : 0,
            // Boolean as integer
            '_rx_option_review_criteria' => $mergedCriteria,
        ];
        return $updatedData;
    }
    public function key_exists($option_name)
    {
        $option_value = get_option($option_name);
        return $option_value !== \false;
    }
}
