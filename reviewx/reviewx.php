<?php

namespace Rvx;

/**
 * Plugin Name:       ReviewX – Multi-Criteria Rating & Reviews
 * Plugin URI:        https://reviewx.io
 * Description:       Advanced Multi-Criteria Rating & Reviews for WooCommerce. Turn customer reviews into sales by leveraging reviews with multiple criteria, reminder emails, Google reviews, review schemas, and incentives like discounts.
 * Version:           2.1.5
 * Author:            ReviewX
 * Author URI:        https://reviewx.io
 * Text Domain: reviewx
 * Domain Path: /languages
 * @package     ReviewX
 * @author      ReviewX <support@reviewx.io>
 * @copyright   Copyright (C) 2024 ReviewX & JoulesLabs. All rights reserved.
 * @license     GPLv3 or later
 * @since       1.0.0
 */
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
// don't call the file directly
\defined('ABSPATH') || die;
\define('RVX_VERSION', '2.1.5');
\define('RVX_DIR_PATH', plugin_dir_path(__FILE__));
\define('RVX_DIR_NAME', \basename(\RVX_DIR_PATH));
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
    // Execute when plugin loaded/activated before runnning anything else
    (new \Rvx\Handlers\RvxInit\LoadReviewxCreateSiteTable())->__invoke();
    \call_user_func(function ($bootstrap) {
        $bootstrap(__FILE__);
    }, require __DIR__ . '/bootstrap/boot.php');
}
rvx_wpdrill_init();
require_once \ABSPATH . 'wp-admin/includes/image.php';
if (Client::getSync() === \true) {
    add_action('init', function () : void {
        $data = get_option('_rvx_custom_post_type_settings');
        $enabled_post_types = ['product'];
        if ($data && Helper::arrayGet($data, 'code') !== 40000) {
            foreach ($data['data']['reviews'] as $review) {
                if ($review['status'] === 'Enabled') {
                    $enabled_post_types[] = $review['post_type'];
                }
            }
        }
        foreach ($enabled_post_types as $post_type) {
            add_post_type_support($post_type, 'comments');
        }
    });
    add_filter('comments_template', function ($default) {
        $data = get_option('_rvx_custom_post_type_settings');
        $enabled_post_types = ['product'];
        if ($data && Helper::arrayGet($data, 'code') !== 40000) {
            foreach ($data['data']['reviews'] as $review) {
                if ($review['status'] === 'Enabled') {
                    $enabled_post_types[] = $review['post_type'];
                }
            }
        }
        if (is_singular($enabled_post_types)) {
            if (\class_exists('WooCommerce') && is_account_page()) {
                // Do nothing on woocommerce front-end user dashboard
            } else {
                (new \Rvx\Handlers\CommentBoxHandle())->__invoke();
                return \dirname(__FILE__) . '/widget.php';
                // Path to your custom template
            }
        }
        return $default;
    }, \PHP_INT_MAX);
}
