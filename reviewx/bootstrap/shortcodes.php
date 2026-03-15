<?php

namespace ReviewX;

\defined('ABSPATH') || exit;
use ReviewX\WPDrill\Facades\Shortcode;
use ReviewX\WPDrill\Plugin;
return function (Plugin $plugin) {
    Shortcode::add('rvx-reviews', \ReviewX\Shortcodes\Products\ReviewShowWIthIdsShortcode::class);
    Shortcode::add('rvx-review-list', \ReviewX\Shortcodes\Products\ReviewListShortcode::class);
    Shortcode::add('rvx-criteria-graph', \ReviewX\Shortcodes\Products\ReviewGraphShortcode::class);
    Shortcode::add('rvx-summary', \ReviewX\Shortcodes\Products\ReviewSummaryShortcode::class);
    Shortcode::add('rvx-stats', \ReviewX\Shortcodes\Products\ReviewStatshortcode::class);
    Shortcode::add('rvx-star-count', \ReviewX\Shortcodes\Products\ReviewStarCountShortcode::class);
    Shortcode::add('rvx-google-review', \ReviewX\Shortcodes\GoogleReviewLIst::class);
    Shortcode::add('rvx-review-form', \ReviewX\Shortcodes\Products\ReviewListFormShortcode::class);
    Shortcode::add('rvx-woo-reviews', \ReviewX\Shortcodes\Products\WooReviewsFormShortcode::class);
    // Shortcode::add('rvx_user_avatar', \ReviewX\Shortcodes\Users\UserAvatarShortcode::class); // future implementation
};
