<?php

namespace Rvx\Handlers\MigrationRollback;

class SharedMethods
{
    public function __construct()
    {
        // Constructor code if needed
    }
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
            if (is_wp_error($result)) {
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
            if (is_wp_error($result)) {
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
        $multicrtriaEnableorDisale = get_option('_rx_option_allow_multi_criteria');
        $newCriteria = ["enable" => $multicrtriaEnableorDisale == 1 ? \true : \false, "criterias" => $criterias];
        return $newCriteria;
    }
    /*
        public function old_rvxRollbackReverseReviewCriteriaConverter($newCriteria)
        {
            global $wpdb;
            
            // Initialize old criteria structure
            $oldCriteria = [];
            
            // Deserialize old _rx_option_review_criteria if it exists
            $existingOldData = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", '_rx_option_review_criteria' ) );
            
            // result# string(62) "a:3:{s:8:"ctr_h8S7";s:7:"Quality";s:8:"ctr_h8S8";s:5:"Price";}"
    
            if ($existingOldData) {
                $oldCriteria = maybe_unserialize($existingOldData); // Deserialize existing criteria
                //$oldCriteria = "a:3:{s:8:"ctr_h8S7";s:7:"Quality";s:8:"ctr_h8S8";s:5:"Price";}"; // Deserialize existing criteria
            }
        
            //dd($existingOldData, $oldCriteria);
            // result# 
    
            // Find the highest number in the old criteria keys (ctr_h8S7, ctr_h8S8, etc.)
            $highestNumber = 0;
            foreach ($oldCriteria as $key => $name) {
                if (preg_match('/ctr_h8S(\d+)/', $key, $matches)) {
                    $highestNumber = max($highestNumber, (int)$matches[1]); // Get the highest number from the old keys
                }
            }
        
            // Start assigning new keys from the next number, keeping the existing ones
            $newKeyBase = 'ctr_h8S';
            $newKeyIndex = $highestNumber + 1; // Start from the next number after the highest existing one
            
            // Add new criteria from the new data
            foreach ($newCriteria['criterias'] as $criteria) {
                if (isset($criteria['key'], $criteria['name'])) {
                    $newKey = $newKeyBase . $newKeyIndex;
                    // Check if the new key already exists; if so, increment the index further
                    while (isset($oldCriteria[$newKey])) {
                        $newKeyIndex++;
                        $newKey = $newKeyBase . $newKeyIndex;
                    }
                    $oldCriteria[$newKey] = $criteria['name']; // Add new criteria with new key
                    $newKeyIndex++; // Increment the index for the next new key
                }
            }
    
            dd ($existingOldData, $oldCriteria, serialize($oldCriteria));
            // result: string(93) "a:3:{s:8:"ctr_h8S1";s:7:"Quality";s:8:"ctr_h8S2";s:5:"Price";s:8:"ctr_h8S3";s:9:"Packaging";}"
            // you see here the key increment is from 1, 2, 3 but, need from previous existing numbers.
        
            // Build old data format
            $oldData = [
                '_rx_option_allow_multi_criteria' => $newCriteria['enable'] ? 1 : 0, // Boolean as integer
                '_rx_option_review_criteria' => serialize($oldCriteria), // Serialize the updated criteria
            ];
            
            return $oldData;
        }
    
        public function works_rvxRollbackReverseReviewCriteriaConverter($newCriteria)
        {
            //global $wpdb;
            
            // Initialize old criteria structure
            $oldCriteria = [];
            
            // Retrieve existing criteria from the database
            $existingOldData = get_option('_rx_option_review_criteria');
            //$existingOldData = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", '_rx_option_review_criteria' ) );
            
            if ($existingOldData) {
                $oldCriteria = maybe_unserialize($existingOldData); // Deserialize existing criteria
            }
        
            // Find the highest number in the old criteria keys (ctr_h8S7, ctr_h8S8, etc.)
            $highestNumber = 0;
            foreach ($oldCriteria as $key => $name) {
                if (preg_match('/ctr_h8S(\d+)/', $key, $matches)) {
                    $highestNumber = max($highestNumber, (int)$matches[1]); // Get the highest number from the old keys
                }
            }
    
            // Start assigning new keys from the next number, keeping the existing ones
            $newKeyBase = 'ctr_h8S';
            $newKeyIndex = $highestNumber + 1; // Start from the next number after the highest existing one
            // Add new criteria from the new data
            foreach ($newCriteria['criterias'] as $criteria) {
                if (isset($criteria['key'], $criteria['name'])) {
                    // Check if the new criteria already has a key, otherwise create it with the next available number
                    $newKey = $newKeyBase . $newKeyIndex;
                  
                    // Avoid overwriting any existing criteria keys
                    while (isset($oldCriteria[$newKey])) {
                        $newKeyIndex++;
                        $newKey = $newKeyBase . $newKeyIndex;
                    }
                   
                    // Add new criteria with the new key
                    $oldCriteria[$newKey] = $criteria['name']; 
                  
                    $newKeyIndex++; // Increment the index for the next new key
                }
            }
    
            $uniqueCriteria = [];
            foreach ($oldCriteria as $key => $value) {
                if (!in_array($value, $uniqueCriteria)) {
                    $uniqueCriteria[$key] = $value; // Add unique value with its key
                }
            }
            
            // Build old data format
            $oldData = [
                '_rx_option_allow_multi_criteria' => $newCriteria['enable'] ? 1 : 0, // Boolean as integer
                '_rx_option_review_criteria' => $uniqueCriteria, // Serialize the updated criteria
            ];
            
            //dd($oldData);
        
            return $oldData;
        }
    */
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
