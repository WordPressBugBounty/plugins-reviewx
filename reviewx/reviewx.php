<?php

namespace Rvx;

/**
 * Plugin Name: ReviewX – Multi-Criteria Rating & Reviews
 * Plugin URI:  https://reviewx.io
 * Description: Advanced Multi-Criteria Rating & Reviews for WooCommerce. Turn customer reviews into sales by leveraging reviews with multiple criteria, reminder emails, Google reviews, review schemas, and incentives like discounts.
 * Version:     2.3.2
 * Author:      ReviewX
 * Author URI:  https://reviewx.io
 * Text Domain: reviewx
 * Domain Path: /languages
 * @package     ReviewX
 * @author      ReviewX <support@reviewx.io>
 * @copyright   Copyright (C) 2024 ReviewX & JoulesLabs. All rights reserved.
 * @license     GPLv3 or later
 * @since       1.0.0
 */
@\ini_set('display_errors', 0);
// don't call the file directly
\defined('ABSPATH') || die;
\define('RVX_VERSION', '2.3.2');
\define('RVX_DIR_PATH', plugin_dir_path(__FILE__));
\define('RVX_DIR_NAME', \basename(\RVX_DIR_PATH));
\define('RVX_PREFIX', 'rvx_');
\define('RVX_FILE', __FILE__);
\define('RVX_URL', plugins_url('/', __FILE__));
\define('RVX_CUSTOMIZER_URL', \RVX_URL . 'app/Customize/');
if (\php_sapi_name() === 'cli') {
    return;
}
// Load Composer
require __DIR__ . '/vendor/autoload.php';
// Silence PHP deprecation warnings from vendor packages
\error_reporting(\error_reporting() & ~\E_DEPRECATED);
// Boot ReviewX
\call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require __DIR__ . '/bootstrap/boot.php');
