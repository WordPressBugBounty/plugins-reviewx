<?php

namespace ReviewX;

\defined('ABSPATH') || exit;
use ReviewX\WPDrill\Plugin;
return function (string $file) {
    $plugin = new Plugin($file);
    \ReviewX\WPDrill\Facade::setFacadeApplication($plugin);
    \ReviewX\WPDrill\Models\Model::setFacadeApplication($plugin);
    $fn = null;
    if (\php_sapi_name() !== 'cli') {
        $fn = function (\ReviewX\WPDrill\Routing\RouteManager $route) {
            $route->loadRoutes();
        };
    }
    $plugin->make($fn);
    return $plugin;
};
