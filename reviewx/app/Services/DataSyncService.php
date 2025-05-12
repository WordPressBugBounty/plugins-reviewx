<?php

namespace Rvx\Services;

use Exception;
use Rvx\Api\DataSyncApi;
use Rvx\Api\WebhookRequestApi;
class DataSyncService extends \Rvx\Services\Service
{
    protected \Rvx\Services\OrderService $orderService;
    protected \Rvx\Services\DiscountSyncService $discountSyncService;
    public function __construct()
    {
        $this->orderService = new \Rvx\Services\OrderService();
        $this->discountSyncService = new \Rvx\Services\DiscountSyncService();
    }
    public function dataSync($from) : bool
    {
        try {
            \mkdir(WP_CONTENT_DIR . '/uploads/reviewx', 0777, \true);
            $file_path = WP_CONTENT_DIR . '/uploads/reviewx/shop-bulk-data.jsonl';
            $file = \fopen($file_path, 'w');
            $totalLines = 0;
            $syncedCaterories = new \Rvx\Services\CategorySyncService();
            $totalLines += $syncedCaterories->syncCategory($file);
            $totalLines += (new \Rvx\Services\UserSyncService())->syncUser($file);
            $processProduct = new \Rvx\Services\ProductSyncService($syncedCaterories);
            $totalLines += $processProduct->processProductForSync($file);
            $totalLines += (new \Rvx\Services\ReviewSyncService())->processReviewForSync($file);
            if (\class_exists('WooCommerce')) {
                $order = new \Rvx\Services\OrderItemSyncService();
                $totalLines += $order->syncOrder($file);
                $totalLines += $order->syncOrderItem($file);
                //  $this->discountSyncService->processDiscountForSync($file);
            }
            \fclose($file);
            /*$file_info = $this->prepareFileInfo($file_path);
              $file = $_FILES['file'] = $file_info;
              $fileUpload = (new DataSyncApi())->dataSync($file, $from, $totalLines);
              if (file_exists($file_path)) {
                 unlink($file_path);
              }*/
            (new WebhookRequestApi())->finishedWebhook(['total_objects' => $totalLines, 'status' => 'finished', 'from' => $from, 'resource_url' => home_url() . '/wp-json/reviewx/api/v1/synced/data']);
            return \true;
            //            return $fileUpload;
        } catch (Exception $e) {
            return \false;
        }
    }
    protected function dataSyncFile($file, $file_path, $from, $totalLines)
    {
        \fclose($file);
        $file_info = $this->prepareFileInfo($file_path);
        $file = $_FILES['file'] = $file_info;
        $fileUpload = (new DataSyncApi())->dataSync($file, $from, $totalLines);
        if (\file_exists($file_path)) {
            \unlink($file_path);
        }
        return $fileUpload;
    }
    private function prepareFileInfo($file_path)
    {
        return ['name' => \basename($file_path), 'full_path' => \realpath($file_path), 'type' => "application/json", 'tmp_name' => $file_path, 'error' => 0, 'size' => \filesize($file_path)];
    }
    public function syncStatus()
    {
        return (new DataSyncApi())->syncStatus();
    }
    public function dataManualSync($data)
    {
        $totalLines = 0;
        $file_path = RVX_DIR_PATH . 'manual_sync.jsonl';
        $file = \fopen($file_path, 'a');
        $order = new \Rvx\Services\OrderItemSyncService();
        if ("categories" === $data['action']) {
            $syncedCaterories = new \Rvx\Services\CategorySyncService();
            $totalLines += $syncedCaterories->syncCategory($file);
            $processProduct = new \Rvx\Services\ProductSyncService($syncedCaterories);
            $totalLines += $processProduct->processProductForSync($file);
            update_option('rvx_sync_number', $totalLines);
        }
        if ("users" === $data['action']) {
            $totalLines = get_option('rvx_sync_number');
            $totalLines += (new \Rvx\Services\UserSyncService())->syncUser($file);
            update_option('rvx_sync_number', $totalLines);
        }
        if ("reviews" === $data['action']) {
            $totalLines = get_option('rvx_sync_number');
            $totalLines += (new \Rvx\Services\ReviewSyncService())->processReviewForSync($file);
            update_option('rvx_sync_number', $totalLines);
        }
        if ("order" === $data['action']) {
            if (\class_exists('WooCommerce')) {
                $totalLines = get_option('rvx_sync_number');
                $totalLines += $order->syncOrder($file);
                $totalLines += $order->syncOrderItem($file);
                update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("api" === $data['action']) {
            $totalLines = get_option('rvx_sync_number');
            return $this->dataSyncFile($file, $file_path, 'register', $totalLines);
        }
    }
    public function ping()
    {
        global $wpdb, $wp_version;
        // Get migration status
        $migration_status = get_option('_rvx_db_upgrade_216', '0');
        $rollback_status = get_option('_rvx_current_rollback', '0');
        return ['environment' => ['php' => ['version' => \PHP_VERSION, 'memory_limit' => \ini_get('memory_limit'), 'max_execution_time' => \ini_get('max_execution_time'), 'extensions' => \get_loaded_extensions(), 'opcache_enabled' => \extension_loaded('opcache') && \opcache_get_status()['opcache_enabled'], 'apcu_enabled' => \extension_loaded('apcu') && \ini_get('apc.enabled')], 'server' => ['software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A', 'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A', 'https' => is_ssl(), 'db_version' => $wpdb->db_version(), 'fs_method' => \defined('FS_METHOD') ? FS_METHOD : 'direct']], 'wordpress' => ['version' => $wp_version, 'locale' => get_locale(), 'multisite' => is_multisite(), 'debug' => ['wp_debug' => WP_DEBUG, 'wp_debug_log' => WP_DEBUG_LOG, 'wp_debug_display' => WP_DEBUG_DISPLAY, 'script_debug' => SCRIPT_DEBUG], 'memory' => ['wp_memory_limit' => WP_MEMORY_LIMIT, 'wp_max_memory_limit' => WP_MAX_MEMORY_LIMIT, 'current_usage' => \round(\memory_get_usage() / 1024 / 1024, 2) . 'M'], 'cron' => ['jobs_count' => \count(_get_cron_array()), 'alternate_wp_cron' => \defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON], 'cache' => $this->get_cache_status()], 'reviewx' => ['version' => RVX_VERSION, 'migration' => ['status' => (bool) $migration_status], 'rollback' => ['status' => (bool) $rollback_status], 'paths' => ['dir_name' => RVX_DIR_NAME, 'dir_path' => RVX_DIR_PATH, 'dir_url' => RVX_URL]], 'plugins' => $this->get_plugins_status(), 'theme' => $this->get_theme_details()];
    }
    private function get_cache_status()
    {
        return ['wp_cache' => \defined('WP_CACHE') && WP_CACHE, 'object_cache' => \file_exists(WP_CONTENT_DIR . '/object-cache.php'), 'browser_cache' => (bool) get_option('gzipcompression', 0), 'plugins' => $this->detect_caching_plugins(), 'server_side' => $this->detect_server_caching()];
    }
    private function detect_caching_plugins()
    {
        $caching_plugins = [];
        $active_plugins = get_option('active_plugins', []);
        $known_caching_plugins = ['w3-total-cache' => 'w3-total-cache/w3-total-cache.php', 'wp-super-cache' => 'wp-super-cache/wp-cache.php', 'wp-rocket' => 'wp-rocket/wp-rocket.php', 'litespeed-cache' => 'litespeed-cache/litespeed-cache.php', 'autoptimize' => 'autoptimize/autoptimize.php'];
        foreach ($known_caching_plugins as $name => $plugin_path) {
            if (\in_array($plugin_path, $active_plugins)) {
                $caching_plugins[$name] = \true;
            }
        }
        return $caching_plugins;
    }
    private function detect_server_caching()
    {
        $headers = \headers_list();
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
        return ['varnish' => isset($_SERVER['HTTP_X_VARNISH']), 'nginx_cache' => \strpos($server_software, 'nginx') !== \false, 'cloudflare' => isset($_SERVER['HTTP_CF_RAY']), 'opcache' => \extension_loaded('opcache') && \opcache_get_status()['opcache_enabled'], 'redis' => \defined('WP_REDIS_HOST'), 'memcached' => \defined('WP_MEMCACHED_HOST')];
    }
    private function test_rest_api()
    {
        $response = wp_remote_get(rest_url('wp/v2/types/post'));
        return ['status' => !is_wp_error($response), 'response_code' => wp_remote_retrieve_response_code($response), 'error' => is_wp_error($response) ? $response->get_error_message() : null];
    }
    private function get_theme_details()
    {
        $theme = wp_get_theme();
        return ['name' => $theme->get('Name'), 'version' => $theme->get('Version'), 'parent_theme' => $theme->parent() ? $theme->parent()->get('Name') : null, 'theme_uri' => $theme->get('ThemeURI'), 'author' => $theme->get('Author'), 'template_dir' => $theme->get_template_directory(), 'stylesheet_dir' => $theme->get_stylesheet_directory()];
    }
    private function get_plugins_status()
    {
        if (!\function_exists('Rvx\\get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = [];
        foreach (get_plugins() as $path => $plugin) {
            if (is_plugin_active($path)) {
                $plugins[$plugin['TextDomain'] ?? $path] = ['version' => $plugin['Version'], 'network_active' => is_plugin_active_for_network($path), 'author' => $plugin['Author'], 'update_available' => $this->check_plugin_update($path, $plugin)];
            }
        }
        return $plugins;
    }
    private function check_plugin_update($plugin_path, $plugin_data)
    {
        $updates = get_site_transient('update_plugins');
        return isset($updates->response[$plugin_path]) ? $updates->response[$plugin_path]->new_version : \false;
    }
}
