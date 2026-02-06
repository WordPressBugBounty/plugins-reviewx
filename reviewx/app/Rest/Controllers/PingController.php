<?php

namespace Rvx\Rest\Controllers;

use Exception;
use WP_REST_Response;
use Rvx\Services\PingService;
use Rvx\Utilities\Helper;
class PingController
{
    protected PingService $pingService;
    public function __construct()
    {
        $this->pingService = new PingService();
    }
    /**
     * Ping from sass and (cached for 2 hours) return site info.
     *
     * @return WP_REST_Response
     */
    public function ping() : WP_REST_Response
    {
        // Cache time-to-live: 2 hours
        $cache_duration = 3600 * 2;
        try {
            // Try to get cache
            $data = \get_transient('rvx_ping_cache');
            if (\false === $data) {
                // Cache miss: fetch fresh data, store and return
                $data = $this->pingService->ping();
                set_transient('rvx_ping_cache', $data, $cache_duration);
            }
            return Helper::rvxApi($data)->success(__('Plugin Active', 'reviewx'), 200);
        } catch (Exception $e) {
            return Helper::rvxApi()->fails(__('Plugin deactivated or uninstalled', 'reviewx'), 404);
        }
    }
}
