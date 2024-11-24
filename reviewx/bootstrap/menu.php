<?php

namespace Rvx;

use Rvx\WPDrill\Plugin;
use Rvx\WPDrill\Facades\Menu;
use Rvx\Utilities\Auth\Client;
return function (Plugin $plugin) {
    if (Client::has()) {
        Menu::group(__("ReviewX", "reviewx"), \Rvx\Handlers\DashboardMenuHandler::class, 'manage_options', function (\Rvx\WPDrill\Menus\MenuBuilder $menu) {
            if (!current_user_can('manage_options')) {
                return;
            }
            $menu->currentGroup()->position(2)->icon(\RVX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
            $menu->add(__("Dashboard", "reviewx"), \Rvx\Handlers\DashboardMenuHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx');
            $menu->add(__("Reviews", "reviewx"), \Rvx\Handlers\AllReviewsHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(__("Review Reminder", "reviewx"), \Rvx\Handlers\ReviewReminderEmailHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(__("Discount for Review", "reviewx"), \Rvx\Handlers\DiscountHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(__("Google Review", "reviewx"), \Rvx\Handlers\GoogleReviewsHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_google_review');
            $menu->add(__("Import / Export", "reviewx"), \Rvx\Handlers\ImportExpotHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_import_export');
            $menu->add(__("Custom Post Reviews", "reviewx"), \Rvx\Handlers\CptReviewsHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_cpt_review');
            $menu->add(__("Settings", "reviewx"), \Rvx\Handlers\GeneralSettingHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(__("Rollback to v1", "reviewx"), \Rvx\Handlers\MigrationRollback\RollbackMenuHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_rollback');
        });
    }
    if (!Client::has()) {
        Menu::group(__("ReviewX", "reviewx"), \Rvx\Handlers\OnboardMenuHandler::class, 'manage_options', function (\Rvx\WPDrill\Menus\MenuBuilder $menu) {
            $menu->currentGroup()->position(2)->icon(\RVX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
        });
        add_action('admin_head', function () {
            $current_user = wp_get_current_user();
            $first_name = get_user_meta($current_user->ID, 'first_name', \true);
            $last_name = get_user_meta($current_user->ID, 'last_name', \true);
            $user_data = array('ID' => $current_user->ID, 'display_name' => $current_user->display_name, 'first_name' => $first_name, 'last_name' => $last_name);
            update_option('rvx_stored_user_info', $user_data);
        });
    }
    //    Menu::remove('upload.php');
};
