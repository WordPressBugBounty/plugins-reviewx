<?php

namespace Rvx;

use Rvx\WPDrill\Facades\Shortcode;
use Rvx\WPDrill\Plugin;
return function (Plugin $plugin) {
    Shortcode::add('rvx-criteria-graph', \Rvx\Shortcodes\Products\ReviewGraphShortcode::class);
    Shortcode::add('rvx-stats', \Rvx\Shortcodes\Products\ReviewStatshortcode::class);
    Shortcode::add('rvx-summary', \Rvx\Shortcodes\Products\ReviewSummaryShortcode::class);
    Shortcode::add('rvx-reviews', \Rvx\Shortcodes\Products\ReviewShowWIthIdsShortcode::class);
    Shortcode::add('rvx-review-list', \Rvx\Shortcodes\Products\ReviewListShortcode::class);
    //Shortcode::add('rvx-google-review', \Rvx\Shortcodes\GoogleReviewLIst::class); //
};
