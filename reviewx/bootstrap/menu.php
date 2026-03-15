<?php

namespace ReviewX;

\defined('ABSPATH') || exit;
use ReviewX\WPDrill\Plugin;
use ReviewX\WPDrill\Facades\Menu;
use ReviewX\Utilities\Auth\Client;
return function (Plugin $plugin) {
    if (Client::has()) {
        Menu::group(\__("ReviewX", "reviewx"), \ReviewX\Handlers\DashboardMenuHandler::class, 'manage_options', function (\ReviewX\WPDrill\Menus\MenuBuilder $menu) {
            if (!current_user_can('manage_options')) {
                return;
            }
            $menu->currentGroup()->position(2)->icon(\REVIEWX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
            $menu->add(\__("Dashboard", "reviewx"), \ReviewX\Handlers\DashboardMenuHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx');
            $menu->add(\__("Reviews", "reviewx"), \ReviewX\Handlers\AllReviewsHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Review Reminder", "reviewx"), \ReviewX\Handlers\ReviewReminderEmailHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Discount for Review", "reviewx"), \ReviewX\Handlers\DiscountHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Google Review", "reviewx"), \ReviewX\Handlers\GoogleReviewsHandler::class, 'manage_options')->icon('dashicons-smiley');
            $menu->add(\__("Import / Export", "reviewx"), \ReviewX\Handlers\ImportExpotHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_import_export');
            $menu->add(\__("Custom Post Reviews", "reviewx"), \ReviewX\Handlers\CptReviewsHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_cpt_review');
            $menu->add(\__("Settings", "reviewx"), \ReviewX\Handlers\GeneralSettingHandler::class, 'manage_options')->icon('dashicons-smiley')->slug('reviewx_settings');
        });
    }
    if (!Client::has()) {
        Menu::group(\__("ReviewX", "reviewx"), \ReviewX\Handlers\OnboardMenuHandler::class, 'manage_options', function (\ReviewX\WPDrill\Menus\MenuBuilder $menu) {
            $menu->currentGroup()->position(2)->icon(\REVIEWX_URL . 'resources/assets/logo/ReviewX_dash_icon_white.png');
        });
    }
};
