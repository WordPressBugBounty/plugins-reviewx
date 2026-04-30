<?php
/**
 * Plugin Name: ReviewX – Multi-Criteria Rating & Reviews
 * Plugin URI:  https://reviewx.io
 * Description: Advanced Multi-Criteria Rating & Reviews for WooCommerce. Turn customer reviews into sales by leveraging reviews with multiple criteria, reminder emails, Google reviews, review schemas, and incentives like discounts.
 * Version:     2.3.10
 * Author:      ReviewX
 * Author URI:  https://reviewx.io
 * Text Domain: reviewx
 * Domain Path: /languages
 * @package     ReviewX
 * @author      ReviewX <support@reviewx.io>
 * @copyright   Copyright (C) 2024 ReviewX & JoulesLabs. All rights reserved.
 * License:      GPLv3 or later
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

// don't call the file directly
defined('ABSPATH') || die();

if (defined('REVIEWX_BOOTSTRAPPED')) {
    return;
}

define('REVIEWX_BOOTSTRAPPED', true);

// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
@ini_set('display_errors', 0);

defined('REVIEWX_VERSION') || define('REVIEWX_VERSION', '2.3.10');
defined('REVIEWX_DIR_PATH') || define('REVIEWX_DIR_PATH', plugin_dir_path(__FILE__));
defined('REVIEWX_DIR_NAME') || define('REVIEWX_DIR_NAME', basename(REVIEWX_DIR_PATH));
defined('REVIEWX_PREFIX') || define('REVIEWX_PREFIX', 'rvx_');
defined('REVIEWX_FILE') || define('REVIEWX_FILE', __FILE__);
defined('REVIEWX_URL') || define('REVIEWX_URL', plugins_url('/', __FILE__));
defined('REVIEWX_CUSTOMIZER_URL') || define('REVIEWX_CUSTOMIZER_URL', REVIEWX_URL . 'app/Customize/');

if (php_sapi_name() === 'cli') {
    return;
}

// Load Composer
require_once __DIR__ . '/vendor/autoload.php';

// Silence PHP deprecation warnings from vendor packages
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
error_reporting(error_reporting() & ~E_DEPRECATED);

// Boot ReviewX
call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/bootstrap/boot.php'));
