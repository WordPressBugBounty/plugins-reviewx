<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Rvx\CPT\CptHelper;
use Rvx\Services\Api\LoginService;
use Rvx\Services\DataSyncService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\Migration\Migrator;
use Rvx\Services\CacheServices;
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
        add_action('activated_plugin', function ($plugin) {
            if (RVX_DIR_NAME . '/reviewx.php' !== $plugin) {
                return;
            }
            global $wpdb;
            // Initialize tables and reset sync flag
            (new \Rvx\Handlers\RvxInit\LoadReviewxCreateSiteTable())->init();
            set_transient('rvx_reset_sync_flag', \true, 300);
            // 5 mins TTL
            $this->migrator->run();
            $rvxSites = $wpdb->prefix . 'rvx_sites';
            $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
            if ($uid) {
                // Mark as not synced initially
                $wpdb->update($rvxSites, ['is_saas_sync' => 0], ['uid' => $uid], ['%d'], ['%s']);
                try {
                    // Attempt SaaS activation
                    (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => \defined('RVX_VERSION') ? RVX_VERSION : 'unknown', 'wp_version' => get_bloginfo('version')]);
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
                    $wpdb->query("TRUNCATE TABLE {$rvxSites}");
                    if (\defined('WP_DEBUG') || WP_DEBUG) {
                        \error_log('[ReviewX] Activation warning: AuthApi connection failed - ' . $e->getMessage());
                    }
                }
            }
            // Always clean cache and redirect, even if UID or API failed
            $this->cacheServices->removeCache();
            $this->loginService->resetPostMeta();
            wp_safe_redirect(admin_url('admin.php?page=reviewx'));
            exit;
        }, 15, 1);
    }
}
