<?php

namespace Rvx;

\defined('ABSPATH') || exit;
use Rvx\WPDrill\Plugin;
use Rvx\WPDrill\Facades\Menu;
use Rvx\Utilities\Auth\Client;
return function (Plugin $plugin) {
    if (Client::has()) {
        Menu::group(\__("ReviewX", "reviewx"), \Rvx\Handlers\DashboardMenuHandler::class, 'manage_options', function (\Rvx\WPDrill\Menus\MenuBuilder $menu) {
            if (!current_user_can('manage_options')) {
                return;
            }
            $menu->currentGroup()->position(2)->icon(\RVX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
            $menu->add(\__("Dashboard", "reviewx"), \Rvx\Handlers\DashboardMenuHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx');
            $menu->add(\__("Reviews", "reviewx"), \Rvx\Handlers\AllReviewsHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Review Reminder", "reviewx"), \Rvx\Handlers\ReviewReminderEmailHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Discount for Review", "reviewx"), \Rvx\Handlers\DiscountHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Google Review", "reviewx"), \Rvx\Handlers\GoogleReviewsHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_google_review');
            $menu->add(\__("Import / Export", "reviewx"), \Rvx\Handlers\ImportExpotHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_import_export');
            $menu->add(\__("Custom Post Reviews", "reviewx"), \Rvx\Handlers\CptReviewsHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_cpt_review');
            $menu->add(\__("Settings", "reviewx"), \Rvx\Handlers\GeneralSettingHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_settings');
        });
    }
    if (!Client::has()) {
        Menu::group(\__("ReviewX", "reviewx"), \Rvx\Handlers\OnboardMenuHandler::class, 'manage_options', function (\Rvx\WPDrill\Menus\MenuBuilder $menu) {
            $menu->currentGroup()->position(2)->icon(\RVX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
        });
    }
};
