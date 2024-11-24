<?php

namespace Rvx;

/**
 * Plugin Name:       ReviewX
 * Plugin URI:        https://reviewx.io
 * Description:       Advanced Multi-criteria Rating & Reviews for WooCommerce. Turn your customer reviews into sales by collecting and leveraging reviews, ratings with multiple criteria.
 * Version:           2.0.0
 * Author:            ReviewX
 * Author URI:        https://reviewx.io
 * Text Domain:       reviewx
 * Domain Path:       /languages
 * @package     ReviewX
 * @author      ReviewX <support@reviewx.io>
 * @copyright   Copyright (C) 2024 ReviewX & JoulesLabs. All rights reserved.
 * @license     GPLv3 or later
 * @since       1.0.0
 */
use Rvx\Utilities\Helper;
// don't call the file directly
\defined('ABSPATH') || die;
\define('RVX_VERSION', '2.0.0');
\define('RVX_DIR_PATH', plugin_dir_path(__FILE__));
\define('RVX_PREFIX', 'rvx_');
\define('RVX_FILE', __FILE__);
\define('RVX_URL', plugins_url('/', __FILE__));
\define('RVX_CUSTOMIZER_URL', \RVX_URL . 'app/Customize/');
if (\php_sapi_name() === 'cli') {
    return;
}
function rvx_wpdrill_init()
{
    require __DIR__ . '/vendor/autoload.php';
    \call_user_func(function ($bootstrap) {
        $bootstrap(__FILE__);
    }, require __DIR__ . '/bootstrap/boot.php');
}
rvx_wpdrill_init();
require_once \ABSPATH . 'wp-admin/includes/image.php';
add_filter('comments_template', function ($default) {
    $data = get_option('_rvx_custom_post_type_settings');
    $enabled_post_types = [];
    if ($data && Helper::arrayGet($data, 'code') !== 40000) {
        foreach ($data['data']['reviews'] as $review) {
            if ($review['status'] === 'Enabled') {
                $enabled_post_types[] = $review['post_type'];
            }
        }
    }
    $enabled_post_types[] = 'product';
    if (is_singular($enabled_post_types)) {
        return \dirname(__FILE__) . '/widget.php';
    }
    return $default;
}, 99);
