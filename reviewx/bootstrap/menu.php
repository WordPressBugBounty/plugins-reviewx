<?php

namespace ReviewX;

\defined('ABSPATH') || exit;
use ReviewX\WPDrill\Plugin;
use ReviewX\WPDrill\Facades\Menu;
use ReviewX\Services\CacheServices;
use ReviewX\Utilities\Auth\Client;
return function (Plugin $plugin) {
    $cacheServices = new CacheServices();
    $pendingReviewCount = 0;
    $shouldRenderPendingBadge = Client::has() && $cacheServices->currentUserCanAccessReviewx();
    if ($shouldRenderPendingBadge) {
        $pendingReviewCount = (int) ($cacheServices->pendingReviewNoticeSummary()['pending'] ?? 0);
    }
    if (Client::has()) {
        Menu::group(\__("ReviewX", "reviewx"), \ReviewX\Handlers\DashboardMenuHandler::class, 'manage_options', function (\ReviewX\WPDrill\Menus\MenuBuilder $menu) {
            if (!\current_user_can('manage_options')) {
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
    if ($shouldRenderPendingBadge) {
        \add_action('admin_head', static function () {
            echo '<style>
                #toplevel_page_reviewx .reviewx-admin-notice-badge,
                #toplevel_page_reviewx .reviewx-admin-notice-badge .pending-count {
                    min-width: 18px;
                    line-height: 18px;
                }
                #toplevel_page_reviewx .reviewx-admin-notice-badge {
                    margin-inline-start: 6px;
                    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                    box-shadow: 0 6px 14px rgba(37, 99, 235, 0.18);
                }
                #toplevel_page_reviewx .reviewx-admin-notice-badge[hidden] {
                    display: none !important;
                }
            </style>';
        });
        \add_action('admin_menu', static function () use($pendingReviewCount) {
            global $menu, $submenu;
            $badge = \sprintf(' <span class="awaiting-mod reviewx-admin-notice-badge"%s><span class="pending-count">%d</span></span>', $pendingReviewCount > 0 ? '' : ' hidden', (int) $pendingReviewCount);
            if (\is_array($menu)) {
                foreach ($menu as &$menuItem) {
                    if (($menuItem[2] ?? '') === 'reviewx') {
                        $menuItem[0] .= $badge;
                        break;
                    }
                }
                unset($menuItem);
            }
            if (isset($submenu['reviewx']) && \is_array($submenu['reviewx'])) {
                foreach ($submenu['reviewx'] as &$submenuItem) {
                    $submenuTitle = \wp_strip_all_tags((string) ($submenuItem[0] ?? ''));
                    if ($submenuTitle === \__('Reviews', 'reviewx')) {
                        $submenuItem[0] .= $badge;
                        break;
                    }
                }
                unset($submenuItem);
            }
        }, 999);
    }
};
