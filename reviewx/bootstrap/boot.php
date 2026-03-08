<?php

namespace Rvx;

\defined('ABSPATH') || exit;
use Rvx\WPDrill\Plugin;
return function (string $file) {
    $plugin = new Plugin($file);
    \Rvx\WPDrill\Facade::setFacadeApplication($plugin);
    \Rvx\WPDrill\Models\Model::setFacadeApplication($plugin);
    $fn = null;
    if (\php_sapi_name() !== 'cli') {
        $fn = function (\Rvx\WPDrill\Routing\RouteManager $route) {
            $route->loadRoutes();
        };
    }
    $plugin->make($fn);
    return $plugin;
};
