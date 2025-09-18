<?php

namespace Rvx\CPT;

use Rvx\Rest\Controllers\CptController;
use Rvx\Utilities\Auth\Client;
class CptHelper
{
    public function enabledCPT() : array
    {
        if (!Client::getSync()) {
            return [];
        }
        // Retrieve settings
        $data = get_option('_rvx_cpt_settings');
        // Default enabled post types
        $enabled_post_types = ['product' => 'product'];
        // Validate data before processing
        if (\is_array($data) && isset($data['reviews']) && \is_array($data['reviews'])) {
            foreach ($data['reviews'] as $review) {
                if (isset($review['status'], $review['post_type']) && $review['status'] === 'Enabled' && post_type_exists($review['post_type']) && $review['post_type'] !== 'page') {
                    $enabled_post_types[\strtolower($review['post_type'])] = \strtolower($review['post_type']);
                }
            }
        } else {
            return [];
        }
        return \array_unique($enabled_post_types);
    }
    public function usedCPT($param = 'all')
    {
        if (!Client::getSync()) {
            return [];
        }
        $data = (new CptController())->customPostTypes($param);
        if (!isset($data) || !\is_array($data)) {
            return [];
        }
        // Transform the data
        $formattedData = [];
        foreach ($data as $item) {
            if (isset($item['slug'])) {
                $formattedData[$item['slug']] = $item['slug'];
            }
        }
        return $formattedData;
    }
    public function usedCPTOnSync($param = 'all')
    {
        $data = (new CptController())->customPostTypesOnSync($param);
        if (!isset($data) || !\is_array($data)) {
            return [];
        }
        // Transform the data
        $formattedData = [];
        foreach ($data as $item) {
            if (isset($item['slug'])) {
                $formattedData[$item['slug']] = $item['slug'];
            }
        }
        return $formattedData;
    }
    public function cptSettings() : array
    {
        if (!Client::getSync()) {
            return [];
        }
        // Retrieve settings with default as an empty array
        $data = get_option('_rvx_cpt_settings', []);
        // Ensure the data is always an array
        return \is_array($data) ? $data : [];
    }
    public function cptSettingsOnSync() : array
    {
        // Retrieve settings with default as an empty array
        $data = get_option('_rvx_cpt_settings', []);
        // Ensure the data is always an array
        return \is_array($data) ? $data : [];
    }
    public function getPublicCptList()
    {
        $args = array('public' => \true, '_builtin' => \false);
        $post_types = get_post_types($args, 'objects');
        $result = array();
        if (!empty($post_types)) {
            foreach ($post_types as $post_type) {
                if ($post_type->name !== 'product') {
                    $result[] = array('name' => \ucfirst($post_type->labels->name), 'slug' => \strtolower($post_type->name));
                }
            }
        }
        // Add post type
        $result[] = array('name' => 'Post', 'slug' => 'post');
        return $result;
    }
}
