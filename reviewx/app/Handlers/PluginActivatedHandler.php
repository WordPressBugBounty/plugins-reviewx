<?php

namespace ReviewX\Handlers;

\defined('ABSPATH') || exit;
use ReviewX\Api\AuthApi;
use ReviewX\CPT\CptHelper;
use ReviewX\Services\Api\LoginService;
use ReviewX\Services\DataSyncService;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\DB\Migration\Migrator;
use ReviewX\Services\CacheServices;
use Exception;
class PluginActivatedHandler implements InvokableContract
{
    private Migrator $migrator;
    private DataSyncService $dataSyncService;
    private CacheServices $cacheServices;
    private LoginService $loginService;
    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        $this->dataSyncService = new DataSyncService();
        $this->cacheServices = new CacheServices();
        $this->loginService = new LoginService();
    }
    public function __invoke()
    {
        \add_action('activated_plugin', function ($plugin) {
            if (REVIEWX_DIR_NAME . '/reviewx.php' !== $plugin) {
                return;
            }
            global $wpdb;
            // Initialize tables and reset sync flag
            (new \ReviewX\Handlers\ReviewXInit\LoadReviewxCreateSiteTable())->init();
            \set_transient('rvx_reset_sync_flag', \true, 300);
            // 5 mins TTL
            $this->migrator->run();
            $cache_key = 'rvx_site_uid';
            $uid = \wp_cache_get($cache_key, 'reviewx');
            if (\false === $uid) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name from $wpdb->prefix, safe
                $uid = $wpdb->get_var("SELECT uid FROM {$wpdb->prefix}rvx_sites ORDER BY id DESC LIMIT 1");
                if ($uid) {
                    \wp_cache_set($cache_key, $uid, 'reviewx', 86400);
                    // 1 day
                }
            }
            if ($uid) {
                // Mark as not synced initially
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Local state modification
                $wpdb->update("{$wpdb->prefix}rvx_sites", ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
                try {
                    // Attempt SaaS activation
                    (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => \defined('REVIEWX_VERSION') ? REVIEWX_VERSION : 'unknown', 'wp_version' => \get_bloginfo('version')]);
                    // Start initial sync
                    $dataResponse = $this->dataSyncService->dataSync('login', 'product');
                    if ($dataResponse) {
                        \sleep(1);
                        $enabled_post_types = (new CptHelper())->usedCPTOnSync('used');
                        unset($enabled_post_types['product']);
                        foreach ($enabled_post_types as $post_type) {
                            $this->dataSyncService->dataSync('login', $post_type);
                        }
                    }
                } catch (Exception $e) {
                    // Clean up if AuthApi fails
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from $wpdb->prefix, safe
                    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}rvx_sites");
                }
            }
            // Always clean cache and redirect, even if UID or API failed
            $this->cacheServices->removeCache();
            $this->loginService->resetPostMeta();
            \wp_safe_redirect(\admin_url('admin.php?page=reviewx'));
            exit;
        }, 15, 1);
    }
}
